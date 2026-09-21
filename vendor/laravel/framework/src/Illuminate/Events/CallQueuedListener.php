<?php
/**
 * Illuminate，事件，呼叫队列监听器
 */

namespace Illuminate\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CallQueuedListener implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * The listener class name.
	 * 监听器类名
     *
     * @var string
     */
    public $class;

    /**
     * The listener method.
	 * 监听器方法名
     *
     * @var string
     */
    public $method;

    /**
     * The data to be passed to the listener.
	 * 要传递给侦听器的数据
     *
     * @var array
     */
    public $data;

    /**
     * The number of times the job may be attempted.
	 * 可能尝试该作业的次数
     *
     * @var int
     */
    public $tries;

    /**
     * The maximum number of exceptions allowed, regardless of attempts.
	 * 允许的最大异常数，无论是否尝试。
     *
     * @var int
     */
    public $maxExceptions;

    /**
     * The number of seconds to wait before retrying a job that encountered an uncaught exception.
	 * 在重试遇到未捕获异常的作业之前等待的秒数
     *
     * @var int
     */
    public $backoff;

    /**
     * The timestamp indicating when the job should timeout.
	 * 指示作业何时应该超时的时间戳
     *
     * @var int
     */
    public $retryUntil;

    /**
     * The number of seconds the job can run before timing out.
	 * 作业在超时之前可以运行的秒数
     *
     * @var int
     */
    public $timeout;

    /**
     * Indicates if the job should be encrypted.
	 * 指示作业是否应该加密
     *
     * @var bool
     */
    public $shouldBeEncrypted = false;

    /**
     * Create a new job instance.
	 * 创建一个新的作业实例
     *
     * @param  string  $class
     * @param  string  $method
     * @param  array  $data
     * @return void
     */
    public function __construct($class, $method, $data)
    {
        $this->data = $data;
        $this->class = $class;
        $this->method = $method;
    }

    /**
     * Handle the queued job.
	 * 处理排队作业
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function handle(Container $container)
    {
        $this->prepareData();

        $handler = $this->setJobInstanceIfNecessary(
            $this->job, $container->make($this->class)
        );

        $handler->{$this->method}(...array_values($this->data));
    }

    /**
     * Set the job instance of the given class if necessary.
	 * 如果需要，设置给定类的作业实例。
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  object  $instance
     * @return object
     */
    protected function setJobInstanceIfNecessary(Job $job, $instance)
    {
        if (in_array(InteractsWithQueue::class, class_uses_recursive($instance))) {
            $instance->setJob($job);
        }

        return $instance;
    }

    /**
     * Call the failed method on the job instance.
	 * 在作业实例上调用失败的方法
     *
     * The event instance and the exception will be passed.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function failed($e)
    {
        $this->prepareData();

        $handler = Container::getInstance()->make($this->class);

        $parameters = array_merge(array_values($this->data), [$e]);

        if (method_exists($handler, 'failed')) {
            $handler->failed(...$parameters);
        }
    }

    /**
     * Unserialize the data if needed.
	 * 如果需要，将数据反序列化。
     *
     * @return void
     */
    protected function prepareData()
    {
        if (is_string($this->data)) {
            $this->data = unserialize($this->data);
        }
    }

    /**
     * Get the display name for the queued job.
	 * 获取排队作业的显示名称
     *
     * @return string
     */
    public function displayName()
    {
        return $this->class;
    }

    /**
     * Prepare the instance for cloning.
	 * 为克隆准备实例
     *
     * @return void
     */
    public function __clone()
    {
        $this->data = array_map(function ($data) {
            return is_object($data) ? clone $data : $data;
        }, $this->data);
    }
}
