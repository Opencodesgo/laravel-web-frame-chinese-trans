<?php
/**
 * Illuminate, 测试, 流利的，可断言的 Json
 */

namespace Illuminate\Testing\Fluent;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Illuminate\Testing\AssertableJsonString;
use PHPUnit\Framework\Assert as PHPUnit;

class AssertableJson implements Arrayable
{
    use Concerns\Has,
        Concerns\Matching,
        Concerns\Debugging,
        Concerns\Interaction,
        Macroable,
        Tappable;

    /**
     * The properties in the current scope.
	 * 当前作用域中的属性
     *
     * @var array
     */
    private $props;

    /**
     * The "dot" path to the current scope.
	 * 到当前作用域的"点"路径
     *
     * @var string|null
     */
    private $path;

    /**
     * Create a new fluent, assertable JSON data instance.
	 * 创建一个新的流畅的、可断言的JSON数据实例。
     *
     * @param  array  $props
     * @param  string|null  $path
     * @return void
     */
    protected function __construct(array $props, string $path = null)
    {
        $this->path = $path;
        $this->props = $props;
    }

    /**
     * Compose the absolute "dot" path to the given key.
	 * 组成给定键的绝对"点"路径
     *
     * @param  string  $key
     * @return string
     */
    protected function dotPath(string $key = ''): string
    {
        if (is_null($this->path)) {
            return $key;
        }

        return rtrim(implode('.', [$this->path, $key]), '.');
    }

    /**
     * Retrieve a prop within the current scope using "dot" notation.
	 * 使用"点"表示法检索当前作用域中的道具
     *
     * @param  string|null  $key
     * @return mixed
     */
    protected function prop(string $key = null)
    {
        return Arr::get($this->props, $key);
    }

    /**
     * Instantiate a new "scope" at the path of the given key.
	 * 在给定键的路径上实例化一个新的"作用域"
     *
     * @param  string  $key
     * @param  \Closure  $callback
     * @return $this
     */
    protected function scope(string $key, Closure $callback): self
    {
        $props = $this->prop($key);
        $path = $this->dotPath($key);

        PHPUnit::assertIsArray($props, sprintf('Property [%s] is not scopeable.', $path));

        $scope = new static($props, $path);
        $callback($scope);
        $scope->interacted();

        return $this;
    }

    /**
     * Instantiate a new "scope" on the first child element.
	 * 在第一个子元素上实例化一个新的"作用域"
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function first(Closure $callback): self
    {
        $props = $this->prop();

        $path = $this->dotPath();

        PHPUnit::assertNotEmpty($props, $path === ''
            ? 'Cannot scope directly onto the first element of the root level because it is empty.'
            : sprintf('Cannot scope directly onto the first element of property [%s] because it is empty.', $path)
        );

        $key = array_keys($props)[0];

        $this->interactsWith($key);

        return $this->scope($key, $callback);
    }

    /**
     * Instantiate a new "scope" on each child element.
	 * 在每个子元素上实例化一个新的"作用域"
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function each(Closure $callback): self
    {
        $props = $this->prop();

        $path = $this->dotPath();

        PHPUnit::assertNotEmpty($props, $path === ''
            ? 'Cannot scope directly onto each element of the root level because it is empty.'
            : sprintf('Cannot scope directly onto each element of property [%s] because it is empty.', $path)
        );

        foreach (array_keys($props) as $key) {
            $this->interactsWith($key);

            $this->scope($key, $callback);
        }

        return $this;
    }

    /**
     * Create a new instance from an array.
	 * 从数组创建新实例
     *
     * @param  array  $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new static($data);
    }

    /**
     * Create a new instance from an AssertableJsonString.
	 * 从AssertableJsonString 创建一个新实例
     *
     * @param  \Illuminate\Testing\AssertableJsonString  $json
     * @return static
     */
    public static function fromAssertableJsonString(AssertableJsonString $json): self
    {
        return static::fromArray($json->json());
    }

    /**
     * Get the instance as an array.
	 * 以数组的形式获取实例
     *
     * @return array
     */
    public function toArray()
    {
        return $this->props;
    }
}
