<?php
/**
 * Illuminate，分页，游标
 */

namespace Illuminate\Pagination;

use Illuminate\Contracts\Support\Arrayable;
use UnexpectedValueException;

class Cursor implements Arrayable
{
    /**
     * The parameters associated with the cursor.
	 * 与游标关联的参数
     *
     * @var array
     */
    protected $parameters;

    /**
     * Determine whether the cursor points to the next or previous set of items.
	 * 确定光标是指向下一组项还是上一组项
     *
     * @var bool
     */
    protected $pointsToNextItems;

    /**
     * Create a new cursor instance.
	 * 创建一个新的游标实例
     *
     * @param  array  $parameters
     * @param  bool  $pointsToNextItems
     */
    public function __construct(array $parameters, $pointsToNextItems = true)
    {
        $this->parameters = $parameters;
        $this->pointsToNextItems = $pointsToNextItems;
    }

    /**
     * Get the given parameter from the cursor.
	 * 从游标中获取给定参数
     *
     * @param  string  $parameterName
     * @return string|null
     *
     * @throws \UnexpectedValueException
     */
    public function parameter(string $parameterName)
    {
        if (! array_key_exists($parameterName, $this->parameters)) {
            throw new UnexpectedValueException("Unable to find parameter [{$parameterName}] in pagination item.");
        }

        return $this->parameters[$parameterName];
    }

    /**
     * Get the given parameters from the cursor.
	 * 从游标获取给定的参数
     *
     * @param  array  $parameterNames
     * @return array
     */
    public function parameters(array $parameterNames)
    {
        return collect($parameterNames)->map(function ($parameterName) {
            return $this->parameter($parameterName);
        })->toArray();
    }

    /**
     * Determine whether the cursor points to the next set of items.
	 * 确定光标是否指向下一组项
     *
     * @return bool
     */
    public function pointsToNextItems()
    {
        return $this->pointsToNextItems;
    }

    /**
     * Determine whether the cursor points to the previous set of items.
	 * 确定光标是否指向前一组项
     *
     * @return bool
     */
    public function pointsToPreviousItems()
    {
        return ! $this->pointsToNextItems;
    }

    /**
     * Get the array representation of the cursor.
	 * 获取游标的数组表示形式
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->parameters, [
            '_pointsToNextItems' => $this->pointsToNextItems,
        ]);
    }

    /**
     * Get the encoded string representation of the cursor to construct a URL.
	 * 获取游标的编码字符串表示形式以构造URL
     *
     * @return string
     */
    public function encode()
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($this->toArray())));
    }

    /**
     * Get a cursor instance from the encoded string representation.
	 * 从编码字符串表示获取游标实例
     *
     * @param  string|null  $encodedString
     * @return static|null
     */
    public static function fromEncoded($encodedString)
    {
        if (is_null($encodedString) || ! is_string($encodedString)) {
            return null;
        }

        $parameters = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $encodedString)), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $pointsToNextItems = $parameters['_pointsToNextItems'];

        unset($parameters['_pointsToNextItems']);

        return new static($parameters, $pointsToNextItems);
    }
}
