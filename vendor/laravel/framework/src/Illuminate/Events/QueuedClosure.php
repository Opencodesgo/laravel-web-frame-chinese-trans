<?php
/**
 * Illuminate，事件，队列闭包
 */

namespace Illuminate\Events;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

class QueuedClosure
{
    /**
     * The underlying Closure.
	 * 底层闭包
     *
     * @var \Closure
     */
    public $closure;

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
     * The number of seconds before the job should be made available.
	 * 在作业可用之前的秒数
     *
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    public $delay;

    /**
     * All of the "catch" callbacks for the queued closure.
	 * 队列闭包的所有"catch"回调
     *
     * @var array
     */
    public $catchCallbacks = [];

    /**
     * Create a new queued closure event listener resolver.
	 * 创建一个新的排队闭包事件侦听器解析器
     *
     * @param  \Closure  $closure
     * @return void
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
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
     * Set the desired delay in seconds for the job.
	 * 为作业设置所需的延迟（以秒为单位）
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
     * Specify a callback that should be invoked if the queued listener job fails.
	 * 指定在排队侦听器作业失败时应该调用的回调
     *
     * @param  \Closure  $closure
     * @return $this
     */
    public function catch(Closure $closure)
    {
        $this->catchCallbacks[] = $closure;

        return $this;
    }

    /**
     * Resolve the actual event listener callback.
	 * 解析实际的事件侦听器回调
     *
     * @return \Closure
     */
    public function resolve()
    {
        return function (...$arguments) {
            dispatch(new CallQueuedListener(InvokeQueuedClosure::class, 'handle', [
                'closure' => new SerializableClosure($this->closure),
                'arguments' => $arguments,
                'catch' => collect($this->catchCallbacks)->map(function ($callback) {
                    return new SerializableClosure($callback);
                })->all(),
            ]))->onConnection($this->connection)->onQueue($this->queue)->delay($this->delay);
        };
    }
}
