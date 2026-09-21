<?php
/**
 * Illuminate，队列，工作者选项
 */

namespace Illuminate\Queue;

class WorkerOptions
{
    /**
     * The name of the worker.
	 * 工作者名称
     *
     * @var int
     */
    public $name;

    /**
     * The number of seconds to wait before retrying a job that encountered an uncaught exception.
	 * 在重试遇到未捕获异常的作业之前等待的秒数
     *
     * @var int|int[]
     */
    public $backoff;

    /**
     * The maximum amount of RAM the worker may consume.
	 * 工作线程可能消耗的最大RAM量
     *
     * @var int
     */
    public $memory;

    /**
     * The maximum number of seconds a child worker may run.
	 * 子线程可以运行的最大秒数
     *
     * @var int
     */
    public $timeout;

    /**
     * The number of seconds to wait in between polling the queue.
	 * 轮询队列之间等待的秒数
     *
     * @var int
     */
    public $sleep;

    /**
     * The number of seconds to rest between jobs.
	 * 作业之间的休息秒数
     *
     * @var int
     */
    public $rest;

    /**
     * The maximum number of times a job may be attempted.
	 * 可以尝试作业的最大次数
     *
     * @var int
     */
    public $maxTries;

    /**
     * Indicates if the worker should run in maintenance mode.
	 * 指示工作线程是否应在维护模式下运行
     *
     * @var bool
     */
    public $force;

    /**
     * Indicates if the worker should stop when the queue is empty.
	 * 指示工作线程是否应该在队列为空时停止
     *
     * @var bool
     */
    public $stopWhenEmpty;

    /**
     * The maximum number of jobs to run.
	 * 要运行的最大作业数
     *
     * @var int
     */
    public $maxJobs;

    /**
     * The maximum number of seconds a worker may live.
	 * 一个工作线程可以存活的最大秒数
     *
     * @var int
     */
    public $maxTime;

    /**
     * Create a new worker options instance.
	 * 创建一个新的工作者选项实例
     *
     * @param  string  $name
     * @param  int|int[]  $backoff
     * @param  int  $memory
     * @param  int  $timeout
     * @param  int  $sleep
     * @param  int  $maxTries
     * @param  bool  $force
     * @param  bool  $stopWhenEmpty
     * @param  int  $maxJobs
     * @param  int  $maxTime
     * @param  int  $rest
     * @return void
     */
    public function __construct($name = 'default', $backoff = 0, $memory = 128, $timeout = 60, $sleep = 3, $maxTries = 1,
                                $force = false, $stopWhenEmpty = false, $maxJobs = 0, $maxTime = 0, $rest = 0)
    {
        $this->name = $name;
        $this->backoff = $backoff;
        $this->sleep = $sleep;
        $this->rest = $rest;
        $this->force = $force;
        $this->memory = $memory;
        $this->timeout = $timeout;
        $this->maxTries = $maxTries;
        $this->stopWhenEmpty = $stopWhenEmpty;
        $this->maxJobs = $maxJobs;
        $this->maxTime = $maxTime;
    }
}
