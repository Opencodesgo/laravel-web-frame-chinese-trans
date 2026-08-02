<?php
/**
 * Illuminate，视图，组件属性包
 */

namespace Illuminate\View;

use ArrayAccess;
use ArrayIterator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use IteratorAggregate;

class ComponentAttributeBag implements ArrayAccess, Htmlable, IteratorAggregate
{
    use Macroable;

    /**
     * The raw array of attributes.
	 * 属性原始数组
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Create a new component attribute bag instance.
	 * 创建一个新的组件属性包实例
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the first attribute's value.
	 * 得到第一个属性值 
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function first($default = null)
    {
        return $this->getIterator()->current() ?? value($default);
    }

    /**
     * Get a given attribute from the attribute array.
	 * 从属性数组中获取给定的属性
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->attributes[$key] ?? value($default);
    }

    /**
     * Only include the given attribute from the attribute array.
	 * 只包含属性数组中的给定属性
     *
     * @param  mixed|array  $keys
     * @return static
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            $values = $this->attributes;
        } else {
            $keys = Arr::wrap($keys);

            $values = Arr::only($this->attributes, $keys);
        }

        return new static($values);
    }

    /**
     * Exclude the given attribute from the attribute array.
	 * 从属性数组中排除给定的属性
     *
     * @param  mixed|array  $keys
     * @return static
     */
    public function except($keys)
    {
        if (is_null($keys)) {
            $values = $this->attributes;
        } else {
            $keys = Arr::wrap($keys);

            $values = Arr::except($this->attributes, $keys);
        }

        return new static($values);
    }

    /**
     * Filter the attributes, returning a bag of attributes that pass the filter.
	 * 筛选属性，返回通过筛选的属性包。
     *
     * @param  callable  $callback
     * @return static
     */
    public function filter($callback)
    {
        return new static(collect($this->attributes)->filter($callback)->all());
    }

    /**
     * Return a bag of attributes that have keys starting with the given value / pattern.
	 * 返回一个属性包，这些属性的键以给定的值/模式开始。
     *
     * @param  string  $string
     * @return static
     */
    public function whereStartsWith($string)
    {
        return $this->filter(function ($value, $key) use ($string) {
            return Str::startsWith($key, $string);
        });
    }

    /**
     * Return a bag of attributes with keys that do not start with the given value / pattern.
	 * 返回一个属性包，其中的键不是以给定的值/模式开始的。
     *
     * @param  string  $string
     * @return static
     */
    public function whereDoesntStartWith($string)
    {
        return $this->filter(function ($value, $key) use ($string) {
            return ! Str::startsWith($key, $string);
        });
    }

    /**
     * Return a bag of attributes that have keys starting with the given value / pattern.
	 * 返回一个属性包，这些属性的键以给定的值/模式开始。
     *
     * @param  string  $string
     * @return static
     */
    public function thatStartWith($string)
    {
        return $this->whereStartsWith($string);
    }

    /**
     * Exclude the given attribute from the attribute array.
	 * 从属性数组中排除给定的属性
     *
     * @param  mixed|array  $keys
     * @return static
     */
    public function exceptProps($keys)
    {
        $props = [];

        foreach ($keys as $key => $defaultValue) {
            $key = is_numeric($key) ? $defaultValue : $key;

            $props[] = $key;
            $props[] = Str::kebab($key);
        }

        return $this->except($props);
    }

    /**
     * Merge additional attributes / values into the attribute bag.
	 * 将其他属性/值合并到属性包中
     *
     * @param  array  $attributeDefaults
     * @return static
     */
    public function merge(array $attributeDefaults = [])
    {
        $attributes = [];

        $attributeDefaults = array_map(function ($value) {
            if (is_null($value) || is_bool($value)) {
                return $value;
            }

            return e($value);
        }, $attributeDefaults);

        foreach ($this->attributes as $key => $value) {
            if ($key !== 'class') {
                $attributes[$key] = $value;

                continue;
            }

            $attributes[$key] = implode(' ', array_unique(
                array_filter([$attributeDefaults[$key] ?? '', $value])
            ));
        }

        return new static(array_merge($attributeDefaults, $attributes));
    }

    /**
     * Set the underlying attributes.
	 * 设置基础属性
     *
     * @param  array  $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get content as a string of HTML.
	 * 获取HTML字符串形式的内容
     *
     * @return string
     */
    public function toHtml()
    {
        return (string) $this;
    }

    /**
     * Merge additional attributes / values into the attribute bag.
	 * 将其他属性/值合并到属性包中
     *
     * @param  array  $attributeDefaults
     * @return \Illuminate\Support\HtmlString
     */
    public function __invoke(array $attributeDefaults = [])
    {
        return new HtmlString((string) $this->merge($attributeDefaults));
    }

    /**
     * Determine if the given offset exists.
	 * 确定给定偏移量是否存在
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value at the given offset.
	 * 得到给定偏移量的值
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set the value at a given offset.
	 * 设置给定偏移量的值
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Remove the value at the given offset.
	 * 移除给定偏移量的值
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Get an iterator for the items.
	 * 获取项的迭代器
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Implode the attributes into a single HTML ready string.
	 * 将这些属性内爆为单个HTML字符串
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';

        foreach ($this->attributes as $key => $value) {
            if ($value === false || is_null($value)) {
                continue;
            }

            if ($value === true) {
                $value = $key;
            }

            $string .= ' '.$key.'="'.str_replace('"', '\\"', trim($value)).'"';
        }

        return trim($string);
    }
}
