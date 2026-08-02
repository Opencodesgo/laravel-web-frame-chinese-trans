<?php
/**
 * Illuminate，契约，队列，监视器
 */

namespace Illuminate\Contracts\Queue;

interface Monitor
{
    /**
     * Register a callback to be executed on every iteration through the queue loop.
	 * 注册一个回调函数，在队列循环的每次迭代中执行。
     *
     * @param  mixed  $callback
     * @return void
     */
    public function looping($callback);

    /**
     * Register a callback to be executed when a job fails after the maximum amount of retries.
	 * 注册一个回调函数，当作业在重试的最大次数之后失败时执行。
     *
     * @param  mixed  $callback
     * @return void
     */
    public function failing($callback);

    /**
     * Register a callback to be executed when a daemon queue is stopping.
	 * 注册一个回调，以便在守护进程队列停止时执行。
     *
     * @param  mixed  $callback
     * @return void
     */
    public function stopping($callback);
}
