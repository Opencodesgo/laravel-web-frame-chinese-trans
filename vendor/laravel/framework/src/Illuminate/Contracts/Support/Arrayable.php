<?php
/**
 * Illuminate，契约，支持，可数组
 */

namespace Illuminate\Contracts\Support;

interface Arrayable
{
    /**
     * Get the instance as an array.
	 * 以数组的形式获取实例
     *
     * @return array
     */
    public function toArray();
}
