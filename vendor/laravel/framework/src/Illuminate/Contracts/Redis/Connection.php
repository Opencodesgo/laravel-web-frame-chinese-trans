<?php
/**
 * Illuminate，契约，Redis，连接
 */

namespace Illuminate\Contracts\Redis;

use Closure;

interface Connection
{
    /**
     * Subscribe to a set of given channels for messages.
	 * 订阅消息给定的信道
     *
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @return void
     */
    public function subscribe($channels, Closure $callback);

    /**
     * Subscribe to a set of given channels with wildcards.
	 * 使用通配符订阅一组给定的通道
     *
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @return void
     */
    public function psubscribe($channels, Closure $callback);

    /**
     * Run a command against the Redis database.
	 * 对Redis数据库运行命令
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function command($method, array $parameters = []);
}
