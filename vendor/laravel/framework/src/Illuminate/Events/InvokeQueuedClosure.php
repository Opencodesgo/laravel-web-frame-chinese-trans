<?php
/**
 * Illuminate，事件，调度队列闭包
 */

namespace Illuminate\Events;

class InvokeQueuedClosure
{
    /**
     * Handle the event.
	 * 处理事件
     *
     * @param  \Laravel\SerializableClosure\SerializableClosure  $closure
     * @param  array  $arguments
     * @return void
     */
    public function handle($closure, array $arguments)
    {
        call_user_func($closure->getClosure(), ...$arguments);
    }

    /**
     * Handle a job failure.
	 * 处理作业失败
     *
     * @param  \Laravel\SerializableClosure\SerializableClosure  $closure
     * @param  array  $arguments
     * @param  array  $catchCallbacks
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed($closure, array $arguments, array $catchCallbacks, $exception)
    {
        $arguments[] = $exception;

        collect($catchCallbacks)->each->__invoke(...$arguments);
    }
}
