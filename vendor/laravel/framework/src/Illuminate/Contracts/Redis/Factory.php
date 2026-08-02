<?php
/**
 * Illuminate，契约，Redis，工厂
 */

namespace Illuminate\Contracts\Redis;

interface Factory
{
    /**
     * Get a Redis connection by name.
	 * 通过名称获取Redis连接
     *
     * @param  string|null  $name
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connection($name = null);
}
