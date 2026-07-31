<?php
/**
 * Illuminate，基础，总线，等待调度
 */

namespace Illuminate\Foundation\Bus;

use Illuminate\Bus\UniqueLock;
use Illuminate\Container\Container;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class PendingDispatch
{
    /**
     * The job.
	 * 作业
     *
     * @var mixed
     */
    protected $job;

    /**
     * Indicates if the job should be dispatched immediately after sending the response.
	 * 指明是否应在发送响应后立即分派作业
     *
     * @var bool
     */
    protected $afterResponse = false;

    /**
     * Create a new pending job dispatch.
	 * 创建一个新的挂起作业调度
     *
     * @param  mixed  $job
     * @return void
     */
    public function __construct($job)
    {
        $this->job = $job;
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
        $this->job->onConnection($connection);

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
        $this->job->onQueue($queue);

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
        $this->job->allOnConnection($connection);

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
        $this->job->allOnQueue($queue);

        return $this;
    }

    /**
     * Set the desired delay for the job.
	 * 为作业设置所需的延迟
     *
     * @param  \DateTimeInterface|\DateInterval|int|null  $delay
     * @return $this
     */
    public function delay($delay)
    {
        $this->job->delay($delay);

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
        $this->job->afterCommit();

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
        $this->job->beforeCommit();

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
        $this->job->chain($chain);

        return $this;
    }

    /**
     * Indicate that the job should be dispatched after the response is sent to the browser.
	 * 指明应该在响应发送到浏览器后分派作业
     *
     * @return $this
     */
    public function afterResponse()
    {
        $this->afterResponse = true;

        return $this;
    }

    /**
     * Determine if the job should be dispatched.
	 * 确定是否应该分派作业
     *
     * @return bool
     */
    protected function shouldDispatch()
    {
        if (! $this->job instanceof ShouldBeUnique) {
            return true;
        }

        return (new UniqueLock(Container::getInstance()->make(Cache::class)))
                    ->acquire($this->job);
    }

    /**
     * Dynamically proxy methods to the underlying job.
	 * 将方法动态代理到底层作业
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->job->{$method}(...$parameters);

        return $this;
    }

    /**
     * Handle the object's destruction.
	 * 处理对象的销毁
     *
     * @return void
     */
    public function __destruct()
    {
        if (! $this->shouldDispatch()) {
            return;
        } elseif ($this->afterResponse) {
            app(Dispatcher::class)->dispatchAfterResponse($this->job);
        } else {
            app(Dispatcher::class)->dispatch($this->job);
        }
    }
}
