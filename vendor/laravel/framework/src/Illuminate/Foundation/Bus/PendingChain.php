<?php
/**
 * Illuminate，基础，总线，等待链
 */

namespace Illuminate\Foundation\Bus;

use Closure;
use Illuminate\Queue\CallQueuedClosure;

class PendingChain
{
    /**
     * The class name of the job being dispatched.
	 * 正在分派的作业的类名
     *
     * @var mixed
     */
    public $job;

    /**
     * The jobs to be chained.
	 * 被链接的作业
     *
     * @var array
     */
    public $chain;

    /**
     * Create a new PendingChain instance.
	 * 创建一个新的PendingChain实例
     *
     * @param  mixed  $job
     * @param  array  $chain
     * @return void
     */
    public function __construct($job, $chain)
    {
        $this->job = $job;
        $this->chain = $chain;
    }

    /**
     * Dispatch the job with the given arguments.
	 * 使用给定的参数调度作业
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public function dispatch()
    {
        if (is_string($this->job)) {
            $firstJob = new $this->job(...func_get_args());
        } elseif ($this->job instanceof Closure) {
            $firstJob = CallQueuedClosure::create($this->job);
        } else {
            $firstJob = $this->job;
        }

        return (new PendingDispatch($firstJob))->chain($this->chain);
    }
}
