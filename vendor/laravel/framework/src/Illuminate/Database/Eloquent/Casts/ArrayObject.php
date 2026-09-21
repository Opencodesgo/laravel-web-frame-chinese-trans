<?php
/**
 * Illuminate，数据库，Eloquent，生效，数组对象
 */

namespace Illuminate\Database\Eloquent\Casts;

use ArrayObject as BaseArrayObject;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @template TKey of array-key
 * @template TItem
 *
 * @extends  \ArrayObject<TKey, TItem>
 */
class ArrayObject extends BaseArrayObject implements Arrayable, JsonSerializable
{
    /**
     * Get a collection containing the underlying array.
	 * 获取包含基础数组的集合
     *
     * @return \Illuminate\Support\Collection
     */
    public function collect()
    {
        return collect($this->getArrayCopy());
    }

    /**
     * Get the instance as an array.
	 * 以数组的形式获取实例
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * Get the array that should be JSON serialized.
	 * 获取应该被JSON序列化的数组
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
