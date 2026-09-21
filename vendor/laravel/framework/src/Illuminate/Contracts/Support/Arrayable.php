<?php
/**
 * Illuminate，契约，支持，可数组
 */

namespace Illuminate\Contracts\Support;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface Arrayable
{
    /**
     * Get the instance as an array.
	 * 得到实例为数组
     *
     * @return array<TKey, TValue>
     */
    public function toArray();
}
