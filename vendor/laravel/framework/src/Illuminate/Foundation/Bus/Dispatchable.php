<?php
/**
 * Illuminate，基础，总线，调度单元特征
 */

namespace Illuminate\Foundation\Bus;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Fluent;

trait Dispatchable
{
    /**
     * Dispatch the job with the given arguments.
	 * 使用给定的参数调度任务
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public static function dispatch(...$arguments)
    {
        return new PendingDispatch(new static(...$arguments));
    }

    /**
     * Dispatch the job with the given arguments if the given truth test passes.
	 * 如果给定的真值测试通过，就用给定的参数调度任务。
     *
     * @param  bool  $boolean
     * @param  mixed  ...$arguments
     * @return \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent
     */
    public static function dispatchIf($boolean, ...$arguments)
    {
        return $boolean
            ? new PendingDispatch(new static(...$arguments))
            : new Fluent;
    }

    /**
     * Dispatch the job with the given arguments unless the given truth test passes.
	 * 使用给定的参数调度作业，除非给定的真值测试通过。
     *
     * @param  bool  $boolean
     * @param  mixed  ...$arguments
     * @return \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent
     */
    public static function dispatchUnless($boolean, ...$arguments)
    {
        return ! $boolean
            ? new PendingDispatch(new static(...$arguments))
            : new Fluent;
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
	 * 将命令分派给当前进程中相应的处理程序
     *
     * Queueable jobs will be dispatched to the "sync" queue.
	 * 可排队作业将被分配到"同步"队列
     *
     * @return mixed
     */
    public static function dispatchSync(...$arguments)
    {
        return app(Dispatcher::class)->dispatchSync(new static(...$arguments));
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
	 * 将命令分派给当前进程中相应的处理程序
     *
     * @return mixed
     *
     * @deprecated Will be removed in a future Laravel version.
     */
    public static function dispatchNow(...$arguments)
    {
        return app(Dispatcher::class)->dispatchNow(new static(...$arguments));
    }

    /**
     * Dispatch a command to its appropriate handler after the current process.
	 * 在当前进程结束后，将命令分派给相应的处理程序。
     *
     * @return mixed
     */
    public static function dispatchAfterResponse(...$arguments)
    {
        return app(Dispatcher::class)->dispatchAfterResponse(new static(...$arguments));
    }

    /**
     * Set the jobs that should run if this job is successful.
	 * 设置任务成功时应该运行的任务
     *
     * @param  array  $chain
     * @return \Illuminate\Foundation\Bus\PendingChain
     */
    public static function withChain($chain)
    {
        return new PendingChain(static::class, $chain);
    }
}
