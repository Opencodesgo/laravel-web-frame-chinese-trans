<?php
/**
 * Illuminate，支持，验证输入
 */

namespace Illuminate\Support;

use ArrayIterator;
use Illuminate\Contracts\Support\ValidatedData;
use stdClass;

class ValidatedInput implements ValidatedData
{
    /**
     * The underlying input.
	 * 底层输入
     *
     * @var array
     */
    protected $input;

    /**
     * Create a new validated input container.
	 * 创建一个新的经过验证的输入容器
     *
     * @param  array  $input
     * @return void
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Get a subset containing the provided keys with values from the input data.
	 * 从输入数据中获取包含所提供键值的子集
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function only($keys)
    {
        $results = [];

        $input = $this->input;

        $placeholder = new stdClass;

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            $value = data_get($input, $key, $placeholder);

            if ($value !== $placeholder) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
	 * 获取除指定项数组外的所有输入
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->input;

        Arr::forget($results, $keys);

        return $results;
    }

    /**
     * Merge the validated input with the given array of additional data.
	 * 将验证的输入与给定的附加数据数组合并
     *
     * @param  array  $items
     * @return static
     */
    public function merge(array $items)
    {
        return new static(array_merge($this->input, $items));
    }

    /**
     * Get the input as a collection.
	 * 将输入作为一个集合获取
     *
     * @return \Illuminate\Support\Collection
     */
    public function collect()
    {
        return new Collection($this->input);
    }

    /**
     * Get the raw, underlying input array.
	 * 获取原始的底层输入数组
     *
     * @return array
     */
    public function all()
    {
        return $this->input;
    }

    /**
     * Get the instance as an array.
	 * 以数组的形式获取实例
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * Dynamically access input data.
	 * 动态访问输入数据
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->input[$name];
    }

    /**
     * Dynamically set input data.
	 * 动态设置输入数据
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->input[$name] = $value;
    }

    /**
     * Determine if an input key is set.
	 * 确定是否设置了输入键
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->input[$name]);
    }

    /**
     * Remove an input key.
	 * 移除输入键
     *
     * @param  string  $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->input[$name]);
    }

    /**
     * Determine if an item exists at an offset.
	 * 确定某项是否存在于偏移量处
     *
     * @param  mixed  $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return isset($this->input[$key]);
    }

    /**
     * Get an item at a given offset.
	 * 获取给定偏移量处的项
     *
     * @param  mixed  $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->input[$key];
    }

    /**
     * Set the item at a given offset.
	 * 在给定的偏移量处设置项
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->input[] = $value;
        } else {
            $this->input[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
	 * 在给定的偏移量处取消项的设置
     *
     * @param  string  $key
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        unset($this->input[$key]);
    }

    /**
     * Get an iterator for the input.
	 * 获取输入的迭代器
     *
     * @return \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->input);
    }
}
