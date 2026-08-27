<?php
/**
 * Illuminate，契约，Cookie，排队的工厂
 */

namespace Illuminate\Contracts\Cookie;

interface QueueingFactory extends Factory
{
    /**
     * Queue a cookie to send with the next response.
	 * 将cookie与下一个响应一起排队发送
     *
     * @param  mixed  ...$parameters
     * @return void
     */
    public function queue(...$parameters);

    /**
     * Remove a cookie from the queue.
	 * 从队列中移除一个cookie
     *
     * @param  string  $name
     * @param  string|null  $path
     * @return void
     */
    public function unqueue($name, $path = null);

    /**
     * Get the cookies which have been queued for the next request.
	 * 获取已为下一个请求排队的cookie
     *
     * @return array
     */
    public function getQueuedCookies();
}
