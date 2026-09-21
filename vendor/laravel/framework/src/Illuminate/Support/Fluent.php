<?php
/**
 * Illuminate, 支持, 流畅的
 */

namespace Illuminate\Support;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements \Illuminate\Contracts\Support\Arrayable<TKey, TValue>
 * @implements \ArrayAccess<TKey, TValue>
 */
class Fluent implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    /**
     * All of the attributes set on the fluent instance.
	 * 在流畅实例上设置的所有属性
     *
     * @var array<TKey, TValue>
     */
    protected $attributes = [];

    /**
     * Create a new fluent instance.
	 * 创建一个新的流畅实例
     *
     * @param  iterable<TKey, TValue>  $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get an attribute from the fluent instance.
	 * 从流畅实例获取属性
     *
     * @template TGetDefault
     *
     * @param  TKey  $key
     * @param  TGetDefault|(\Closure(): TGetDefault)  $default
     * @return TValue|TGetDefault
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return value($default);
    }

    /**
     * Get the attributes from the fluent instance.
	 * 从流畅实例获取属性
     *
     * @return array<TKey, TValue>
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Convert the fluent instance to an array.
	 * 将fluent实例转换为数组
     *
     * @return array<TKey, TValue>
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON serializable.
	 * 将对象转换为JSON可序列化的对象
     *
     * @return array<TKey, TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the fluent instance to JSON.
	 * 将fluent实例转换为JSON
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists.
	 * 确定给定的偏移量是否存在
     *
     * @param  TKey  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
	 * 获取给定偏移量的值
     *
     * @param  TKey  $offset
     * @return TValue|null
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
	 * 在给定的偏移量处设置值
     *
     * @param  TKey  $offset
     * @param  TValue  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
	 * 在给定偏移量处取消值的设置
     *
     * @param  TKey  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Handle dynamic calls to the fluent instance to set attributes.
	 * 处理对fluent实例的动态调用以设置属性
     *
     * @param  TKey  $method
     * @param  array{0: ?TValue}  $parameters
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? reset($parameters) : true;

        return $this;
    }

    /**
     * Dynamically retrieve the value of an attribute.
	 * 动态检索属性的值
     *
     * @param  TKey  $key
     * @return TValue|null
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
	 * 动态设置属性的值
     *
     * @param  TKey  $key
     * @param  TValue  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
	 * 动态检查是否设置了属性
     *
     * @param  TKey  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
	 * 动态取消设置属性
     *
     * @param  TKey  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
