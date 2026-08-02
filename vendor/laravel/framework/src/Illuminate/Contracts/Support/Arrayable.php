<?php
/**
 * Illuminate，契约，支持，数组
 */

namespace Illuminate\Contracts\Support;

interface Arrayable
{
    /**
     * Get the instance as an array.
	 * 获取数组形式实例
     *
     * @return array
     */
    public function toArray();
}
