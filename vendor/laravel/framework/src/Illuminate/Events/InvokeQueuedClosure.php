<?php
/**
 * Illuminate，事件，调用队列闭包
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
	 * 处理任务失败
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
