<?php
/**
 * Illuminate，总线，可排队的
 */

namespace Illuminate\Bus;

use Closure;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Arr;
use RuntimeException;

trait Queueable
{
    /**
     * The name of the connection the job should be sent to.
	 * 应该将作业发送到的连接的名称
     *
     * @var string|null
     */
    public $connection;

    /**
     * The name of the queue the job should be sent to.
	 * 应该将作业发送到的队列的名称
     *
     * @var string|null
     */
    public $queue;

    /**
     * The name of the connection the chain should be sent to.
	 * 链应该被发送到的连接的名称
     *
     * @var string|null
     */
    public $chainConnection;

    /**
     * The name of the queue the chain should be sent to.
	 * 链应该被发送到的队列的名称
     *
     * @var string|null
     */
    public $chainQueue;

    /**
     * The callbacks to be executed on chain failure.
	 * 在链失败时执行的回调函数
     *
     * @var array|null
     */
    public $chainCatchCallbacks;

    /**
     * The number of seconds before the job should be made available.
	 * 在作业可用之前的秒数
     *
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    public $delay;

    /**
     * Indicates whether the job should be dispatched after all database transactions have committed.
	 * 指明是否应在所有数据库事务提交后分派作业
     *
     * @var bool|null
     */
    public $afterCommit;

    /**
     * The middleware the job should be dispatched through.
	 * 任务应该通过的中间件进行分派
     *
     * @var array
     */
    public $middleware = [];

    /**
     * The jobs that should run if this job is successful.
	 * 如果此作业成功，应该运行的作业。
     *
     * @var array
     */
    public $chained = [];

    /**
     * Set the desired connection for the job.
	 * 为任务设置所需的连接
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
	 * 为任务设置所需的队列
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
     * Set the desired connection for the chain.
	 * 为链条设置所需的连接
     *
     * @param  string|null  $connection
     * @return $this
     */
    public function allOnConnection($connection)
    {
        $this->chainConnection = $connection;
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the desired queue for the chain.
	 * 为链设置所需的队列
     *
     * @param  string|null  $queue
     * @return $this
     */
    public function allOnQueue($queue)
    {
        $this->chainQueue = $queue;
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set the desired delay for the job.
	 * 为任务设置所需的延迟
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
     * Indicate that the job should be dispatched after all database transactions have committed.
	 * 指明应在所有数据库事务提交后分派作业
     *
     * @return $this
     */
    public function afterCommit()
    {
        $this->afterCommit = true;

        return $this;
    }

    /**
     * Indicate that the job should not wait until database transactions have been committed before dispatching.
	 * 指示作业不应等到数据库事务提交后才进行调度
     *
     * @return $this
     */
    public function beforeCommit()
    {
        $this->afterCommit = false;

        return $this;
    }

    /**
     * Specify the middleware the job should be dispatched through.
	 * 指定应该通过哪个中间件分派作业
     *
     * @param  array|object  $middleware
     * @return $this
     */
    public function through($middleware)
    {
        $this->middleware = Arr::wrap($middleware);

        return $this;
    }

    /**
     * Set the jobs that should run if this job is successful.
	 * 设置任务成功时应该运行的任务
     *
     * @param  array  $chain
     * @return $this
     */
    public function chain($chain)
    {
        $this->chained = collect($chain)->map(function ($job) {
            return $this->serializeJob($job);
        })->all();

        return $this;
    }

    /**
     * Serialize a job for queuing.
	 * 序列化用于排队的作业
     *
     * @param  mixed  $job
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function serializeJob($job)
    {
        if ($job instanceof Closure) {
            if (! class_exists(CallQueuedClosure::class)) {
                throw new RuntimeException(
                    'To enable support for closure jobs, please install the illuminate/queue package.'
                );
            }

            $job = CallQueuedClosure::create($job);
        }

        return serialize($job);
    }

    /**
     * Dispatch the next job on the chain.
	 * 执行链条上的下一个任务
     *
     * @return void
     */
    public function dispatchNextJobInChain()
    {
        if (! empty($this->chained)) {
            dispatch(tap(unserialize(array_shift($this->chained)), function ($next) {
                $next->chained = $this->chained;

                $next->onConnection($next->connection ?: $this->chainConnection);
                $next->onQueue($next->queue ?: $this->chainQueue);

                $next->chainConnection = $this->chainConnection;
                $next->chainQueue = $this->chainQueue;
                $next->chainCatchCallbacks = $this->chainCatchCallbacks;
            }));
        }
    }

    /**
     * Invoke all of the chain's failed job callbacks.
	 * 调用链中所有失败的作业回调
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function invokeChainCatchCallbacks($e)
    {
        collect($this->chainCatchCallbacks)->each(function ($callback) use ($e) {
            $callback($e);
        });
    }
}
