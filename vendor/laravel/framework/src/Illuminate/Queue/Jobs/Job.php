<?php
/**
 * Illuminate，队列，作业，作业抽象类
 */

namespace Illuminate\Queue\Jobs;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\ManuallyFailedException;
use Illuminate\Support\InteractsWithTime;

abstract class Job
{
    use InteractsWithTime;

    /**
     * The job handler instance.
	 * 作业处理程序实例
     *
     * @var mixed
     */
    protected $instance;

    /**
     * The IoC container instance.
	 * IoC容器实例
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Indicates if the job has been deleted.
	 * 指明任务是否已删除
     *
     * @var bool
     */
    protected $deleted = false;

    /**
     * Indicates if the job has been released.
	 * 指明任务是否已释放
     *
     * @var bool
     */
    protected $released = false;

    /**
     * Indicates if the job has failed.
	 * 指明任务是否失败
     *
     * @var bool
     */
    protected $failed = false;

    /**
     * The name of the connection the job belongs to.
	 * 作业所属的连接的名称
     *
     * @var string
     */
    protected $connectionName;

    /**
     * The name of the queue the job belongs to.
	 * 作业所属队列的名称
     *
     * @var string
     */
    protected $queue;

    /**
     * Get the job identifier.
	 * 得到作业标识符
     *
     * @return string
     */
    abstract public function getJobId();

    /**
     * Get the raw body of the job.
	 * 得到作业的原始主体
     *
     * @return string
     */
    abstract public function getRawBody();

    /**
     * Get the UUID of the job.
	 * 得到作业的UUID
     *
     * @return string|null
     */
    public function uuid()
    {
        return $this->payload()['uuid'] ?? null;
    }

    /**
     * Fire the job.
	 * 触发作业
     *
     * @return void
     */
    public function fire()
    {
        $payload = $this->payload();

        [$class, $method] = JobName::parse($payload['job']);

        ($this->instance = $this->resolve($class))->{$method}($this, $payload['data']);
    }

    /**
     * Delete the job from the queue.
	 * 从队列中删除作业
     *
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     * Determine if the job has been deleted.
	 * 确定作业是否已被删除
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Release the job back into the queue.
	 * 将作业释放回队列
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;
    }

    /**
     * Determine if the job was released back into the queue.
	 * 确定作业是否被释放回队列
     *
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * Determine if the job has been deleted or released.
	 * 确定作业是否已被删除或释放
     *
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * Determine if the job has been marked as a failure.
	 * 确定作业是否已被标记为失败
     *
     * @return bool
     */
    public function hasFailed()
    {
        return $this->failed;
    }

    /**
     * Mark the job as "failed".
	 * 把这项工作标记为"失败"
     *
     * @return void
     */
    public function markAsFailed()
    {
        $this->failed = true;
    }

    /**
     * Delete the job, call the "failed" method, and raise the failed job event.
	 * 删除作业，调用"failed"方法，并引发失败的作业事件。
     *
     * @param  \Throwable|null  $e
     * @return void
     */
    public function fail($e = null)
    {
        $this->markAsFailed();

        if ($this->isDeleted()) {
            return;
        }

        try {
            // If the job has failed, we will delete it, call the "failed" method and then call
            // an event indicating the job has failed so it can be logged if needed. This is
            // to allow every developer to better keep monitor of their failed queue jobs.
			// 如果作业失败，我们将删除它，调用"failed"方法，然后调用指示作业失败的事件，以便在需要时对其进行记录。
            $this->delete();

            $this->failed($e);
        } finally {
            $this->resolve(Dispatcher::class)->dispatch(new JobFailed(
                $this->connectionName, $this, $e ?: new ManuallyFailedException
            ));
        }
    }

    /**
     * Process an exception that caused the job to fail.
	 * 处理导致作业失败的异常
     *
     * @param  \Throwable|null  $e
     * @return void
     */
    protected function failed($e)
    {
        $payload = $this->payload();

        [$class, $method] = JobName::parse($payload['job']);

        if (method_exists($this->instance = $this->resolve($class), 'failed')) {
            $this->instance->failed($payload['data'], $e);
        }
    }

    /**
     * Resolve the given class.
	 * 解析给定的类
     *
     * @param  string  $class
     * @return mixed
     */
    protected function resolve($class)
    {
        return $this->container->make($class);
    }

    /**
     * Get the resolved job handler instance.
	 * 获取已解析的作业处理程序实例
     *
     * @return mixed
     */
    public function getResolvedJob()
    {
        return $this->instance;
    }

    /**
     * Get the decoded body of the job.
	 * 拿到解码后的主体
     *
     * @return array
     */
    public function payload()
    {
        return json_decode($this->getRawBody(), true);
    }

    /**
     * Get the number of times to attempt a job.
	 * 获取尝试某项工作的次数
     *
     * @return int|null
     */
    public function maxTries()
    {
        return $this->payload()['maxTries'] ?? null;
    }

    /**
     * Get the number of times to attempt a job after an exception.
	 * 获取在发生异常后尝试作业的次数
     *
     * @return int|null
     */
    public function maxExceptions()
    {
        return $this->payload()['maxExceptions'] ?? null;
    }

    /**
     * Get the number of seconds to delay a failed job before retrying it.
	 * 获取在重试失败作业之前延迟该作业的秒数
     *
     * @return int|null
     */
    public function delaySeconds()
    {
        return $this->payload()['delay'] ?? null;
    }

    /**
     * Get the number of seconds the job can run.
	 * 获取作业可以运行的秒数
     *
     * @return int|null
     */
    public function timeout()
    {
        return $this->payload()['timeout'] ?? null;
    }

    /**
     * Get the timestamp indicating when the job should timeout.
	 * 获取指示作业何时应该超时的时间戳
     *
     * @return int|null
     */
    public function timeoutAt()
    {
        return $this->payload()['timeoutAt'] ?? null;
    }

    /**
     * Get the name of the queued job class.
	 * 获取排队作业类的名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->payload()['job'];
    }

    /**
     * Get the resolved name of the queued job class.
	 * 获取排队作业类的解析名称
     *
     * Resolves the name of "wrapped" jobs such as class-based handlers.
     *
     * @return string
     */
    public function resolveName()
    {
        return JobName::resolve($this->getName(), $this->payload());
    }

    /**
     * Get the name of the connection the job belongs to.
	 * 获取作业所属的连接的名称
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Get the name of the queue the job belongs to.
	 * 获取任务所属队列的名称
     *
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Get the service container instance.
	 * 获取服务容器实例
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
