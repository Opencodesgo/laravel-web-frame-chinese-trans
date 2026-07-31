<?php
/**
 * Illuminate，基础，总线，等待链
 */

namespace Illuminate\Foundation\Bus;

use Closure;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Queue\SerializableClosureFactory;

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
	 * 这些工作将被捆绑起来
     *
     * @var array
     */
    public $chain;

    /**
     * The name of the connection the chain should be sent to.
	 * 应该被发送的链连接名称
     *
     * @var string|null
     */
    public $connection;

    /**
     * The name of the queue the chain should be sent to.
	 * 应该被发送到的链队列名称
     *
     * @var string|null
     */
    public $queue;

    /**
     * The number of seconds before the chain should be made available.
	 * 在链可用之前的秒数
     *
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    public $delay;

    /**
     * The callbacks to be executed on failure.
	 * 失败时要执行的回调
     *
     * @var array
     */
    public $catchCallbacks = [];

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
     * Set the desired connection for the job.
	 * 为作业设置所需的连接
     *
     * @param  string|null  $connection
     * @return $this
     */
    public function onConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the desired queue for the job.
	 * 为作业设置所需的队列
     *
     * @param  string|null  $queue
     * @return $this
     */
    public function onQueue($queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set the desired delay for the chain.
	 * 为链设置所需的延迟
     *
     * @param  \DateTimeInterface|\DateInterval|int|null  $delay
     * @return $this
     */
    public function delay($delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Add a callback to be executed on job failure.
	 * 添加一个在作业失败时执行的回调
     *
     * @param  callable  $callback
     * @return $this
     */
    public function catch($callback)
    {
        $this->catchCallbacks[] = $callback instanceof Closure
                        ? SerializableClosureFactory::make($callback)
                        : $callback;

        return $this;
    }

    /**
     * Get the "catch" callbacks that have been registered.
	 * 获取已注册的"catch"回调
     *
     * @return array
     */
    public function catchCallbacks()
    {
        return $this->catchCallbacks ?? [];
    }

    /**
     * Dispatch the job with the given arguments.
	 * 使用给定的参数调度任务
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

        if ($this->connection) {
            $firstJob->chainConnection = $this->connection;
            $firstJob->connection = $firstJob->connection ?: $this->connection;
        }

        if ($this->queue) {
            $firstJob->chainQueue = $this->queue;
            $firstJob->queue = $firstJob->queue ?: $this->queue;
        }

        if ($this->delay) {
            $firstJob->delay = ! is_null($firstJob->delay) ? $firstJob->delay : $this->delay;
        }

        $firstJob->chain($this->chain);
        $firstJob->chainCatchCallbacks = $this->catchCallbacks();

        return app(Dispatcher::class)->dispatch($firstJob);
    }
}
