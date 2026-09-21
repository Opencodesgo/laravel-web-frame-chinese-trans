<?php
/**
 * Illuminate，队列，工作者
 */

namespace Illuminate\Queue;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Factory as QueueManager;
use Illuminate\Database\DetectsLostConnections;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Carbon;
use Throwable;

class Worker
{
    use DetectsLostConnections;

    const EXIT_SUCCESS = 0;
    const EXIT_ERROR = 1;
    const EXIT_MEMORY_LIMIT = 12;

    /**
     * The name of the worker.
	 * 工作者名称
     *
     * @var string
     */
    protected $name;

    /**
     * The queue manager instance.
	 * 队列管理器实例
     *
     * @var \Illuminate\Contracts\Queue\Factory
     */
    protected $manager;

    /**
     * The event dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The cache repository implementation.
	 * 缓存存储库实现
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * The exception handler instance.
	 * 异常处理程序实例
     *
     * @var \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $exceptions;

    /**
     * The callback used to determine if the application is in maintenance mode.
	 * 用于确定应用程序是否处于维护模式的回调
     *
     * @var callable
     */
    protected $isDownForMaintenance;

    /**
     * The callback used to reset the application's scope.
	 * 用于重置应用程序范围的回调
     *
     * @var callable
     */
    protected $resetScope;

    /**
     * Indicates if the worker should exit.
	 * 指示工作线程是否应该退出
     *
     * @var bool
     */
    public $shouldQuit = false;

    /**
     * Indicates if the worker is paused.
	 * 指示工作线程是否暂停
     *
     * @var bool
     */
    public $paused = false;

    /**
     * The callbacks used to pop jobs from queues.
	 * 回调用于从队列弹出作业
     *
     * @var callable[]
     */
    protected static $popCallbacks = [];

    /**
     * Create a new queue worker.
	 * 创建一个新的队列工作者
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $manager
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  \Illuminate\Contracts\Debug\ExceptionHandler  $exceptions
     * @param  callable  $isDownForMaintenance
     * @param  callable|null  $resetScope
     * @return void
     */
    public function __construct(QueueManager $manager,
                                Dispatcher $events,
                                ExceptionHandler $exceptions,
                                callable $isDownForMaintenance,
                                callable $resetScope = null)
    {
        $this->events = $events;
        $this->manager = $manager;
        $this->exceptions = $exceptions;
        $this->isDownForMaintenance = $isDownForMaintenance;
        $this->resetScope = $resetScope;
    }

    /**
     * Listen to the given queue in a loop.
	 * 在循环中监听给定队列
     *
     * @param  string  $connectionName
     * @param  string  $queue
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return int
     */
    public function daemon($connectionName, $queue, WorkerOptions $options)
    {
        if ($supportsAsyncSignals = $this->supportsAsyncSignals()) {
            $this->listenForSignals();
        }

        $lastRestart = $this->getTimestampOfLastQueueRestart();

        [$startTime, $jobsProcessed] = [hrtime(true) / 1e9, 0];

        while (true) {
            // Before reserving any jobs, we will make sure this queue is not paused and
            // if it is we will just pause this worker for a given amount of time and
            // make sure we do not need to kill this worker process off completely.
			// 在保留任何作业之前，我们将确保这个队列没有暂停。
            if (! $this->daemonShouldRun($options, $connectionName, $queue)) {
                $status = $this->pauseWorker($options, $lastRestart);

                if (! is_null($status)) {
                    return $this->stop($status, $options);
                }

                continue;
            }

            if (isset($this->resetScope)) {
                ($this->resetScope)();
            }

            // First, we will attempt to get the next job off of the queue. We will also
            // register the timeout handler and reset the alarm for this job so it is
            // not stuck in a frozen state forever. Then, we can fire off this job.
			// 首先，我们将尝试从队列中获取下一个作业。
            $job = $this->getNextJob(
                $this->manager->connection($connectionName), $queue
            );

            if ($supportsAsyncSignals) {
                $this->registerTimeoutHandler($job, $options);
            }

            // If the daemon should run (not in maintenance mode, etc.), then we can run
            // fire off this job for processing. Otherwise, we will need to sleep the
            // worker so no more jobs are processed until they should be processed.
			// 如果守护进程应该运行（不是在维护模式，等等）
            if ($job) {
                $jobsProcessed++;

                $this->runJob($job, $connectionName, $options);

                if ($options->rest > 0) {
                    $this->sleep($options->rest);
                }
            } else {
                $this->sleep($options->sleep);
            }

            if ($supportsAsyncSignals) {
                $this->resetTimeoutHandler();
            }

            // Finally, we will check to see if we have exceeded our memory limits or if
            // the queue should restart based on other indications. If so, we'll stop
            // this worker and let whatever is "monitoring" it restart the process.
			// 最后，我们将检查是否超出了内存限制。
            $status = $this->stopIfNecessary(
                $options, $lastRestart, $startTime, $jobsProcessed, $job
            );

            if (! is_null($status)) {
                return $this->stop($status, $options);
            }
        }
    }

    /**
     * Register the worker timeout handler.
	 * 注册工作超时处理程序
     *
     * @param  \Illuminate\Contracts\Queue\Job|null  $job
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return void
     */
    protected function registerTimeoutHandler($job, WorkerOptions $options)
    {
        // We will register a signal handler for the alarm signal so that we can kill this
        // process if it is running too long because it has frozen. This uses the async
        // signals supported in recent versions of PHP to accomplish it conveniently.
		// 我们将为报警信号注册一个信号处理程序。
        pcntl_signal(SIGALRM, function () use ($job, $options) {
            if ($job) {
                $this->markJobAsFailedIfWillExceedMaxAttempts(
                    $job->getConnectionName(), $job, (int) $options->maxTries, $e = $this->maxAttemptsExceededException($job)
                );

                $this->markJobAsFailedIfWillExceedMaxExceptions(
                    $job->getConnectionName(), $job, $e
                );

                $this->markJobAsFailedIfItShouldFailOnTimeout(
                    $job->getConnectionName(), $job, $e
                );
            }

            $this->kill(static::EXIT_ERROR, $options);
        }, true);

        pcntl_alarm(
            max($this->timeoutForJob($job, $options), 0)
        );
    }

    /**
     * Reset the worker timeout handler.
	 * 重置工作超时处理程序
     *
     * @return void
     */
    protected function resetTimeoutHandler()
    {
        pcntl_alarm(0);
    }

    /**
     * Get the appropriate timeout for the given job.
	 * 获取给定作业的适当超时
     *
     * @param  \Illuminate\Contracts\Queue\Job|null  $job
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return int
     */
    protected function timeoutForJob($job, WorkerOptions $options)
    {
        return $job && ! is_null($job->timeout()) ? $job->timeout() : $options->timeout;
    }

    /**
     * Determine if the daemon should process on this iteration.
	 * 确定守护进程是否应该在此迭代中进行处理
     *
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @param  string  $connectionName
     * @param  string  $queue
     * @return bool
     */
    protected function daemonShouldRun(WorkerOptions $options, $connectionName, $queue)
    {
        return ! ((($this->isDownForMaintenance)() && ! $options->force) ||
            $this->paused ||
            $this->events->until(new Looping($connectionName, $queue)) === false);
    }

    /**
     * Pause the worker for the current loop.
	 * 为当前循环暂停工作线程
     *
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @param  int  $lastRestart
     * @return int|null
     */
    protected function pauseWorker(WorkerOptions $options, $lastRestart)
    {
        $this->sleep($options->sleep > 0 ? $options->sleep : 1);

        return $this->stopIfNecessary($options, $lastRestart);
    }

    /**
     * Determine the exit code to stop the process if necessary.
	 * 确定退出代码以在必要时停止进程
     *
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @param  int  $lastRestart
     * @param  int  $startTime
     * @param  int  $jobsProcessed
     * @param  mixed  $job
     * @return int|null
     */
    protected function stopIfNecessary(WorkerOptions $options, $lastRestart, $startTime = 0, $jobsProcessed = 0, $job = null)
    {
        return match (true) {
            $this->shouldQuit => static::EXIT_SUCCESS,
            $this->memoryExceeded($options->memory) => static::EXIT_MEMORY_LIMIT,
            $this->queueShouldRestart($lastRestart) => static::EXIT_SUCCESS,
            $options->stopWhenEmpty && is_null($job) => static::EXIT_SUCCESS,
            $options->maxTime && hrtime(true) / 1e9 - $startTime >= $options->maxTime => static::EXIT_SUCCESS,
            $options->maxJobs && $jobsProcessed >= $options->maxJobs => static::EXIT_SUCCESS,
            default => null
        };
    }

    /**
     * Process the next job on the queue.
	 * 处理队列上的下一个作业
     *
     * @param  string  $connectionName
     * @param  string  $queue
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return void
     */
    public function runNextJob($connectionName, $queue, WorkerOptions $options)
    {
        $job = $this->getNextJob(
            $this->manager->connection($connectionName), $queue
        );

        // If we're able to pull a job off of the stack, we will process it and then return
        // from this method. If there is no job on the queue, we will "sleep" the worker
        // for the specified number of seconds, then keep processing jobs after sleep.
		// 如果我们能够从堆栈中取出一个作业，我们将处理它，然后从方法中返回。
        if ($job) {
            return $this->runJob($job, $connectionName, $options);
        }

        $this->sleep($options->sleep);
    }

    /**
     * Get the next job from the queue connection.
	 * 从队列连接中获取下一个作业
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    protected function getNextJob($connection, $queue)
    {
        $popJobCallback = function ($queue) use ($connection) {
            return $connection->pop($queue);
        };

        try {
            if (isset(static::$popCallbacks[$this->name])) {
                return (static::$popCallbacks[$this->name])($popJobCallback, $queue);
            }

            foreach (explode(',', $queue) as $queue) {
                if (! is_null($job = $popJobCallback($queue))) {
                    return $job;
                }
            }
        } catch (Throwable $e) {
            $this->exceptions->report($e);

            $this->stopWorkerIfLostConnection($e);

            $this->sleep(1);
        }
    }

    /**
     * Process the given job.
	 * 处理给定的作业
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  string  $connectionName
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return void
     */
    protected function runJob($job, $connectionName, WorkerOptions $options)
    {
        try {
            return $this->process($connectionName, $job, $options);
        } catch (Throwable $e) {
            $this->exceptions->report($e);

            $this->stopWorkerIfLostConnection($e);
        }
    }

    /**
     * Stop the worker if we have lost connection to a database.
	 * 如果我们失去了与数据库的连接，则停止worker。
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function stopWorkerIfLostConnection($e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->shouldQuit = true;
        }
    }

    /**
     * Process the given job from the queue.
	 * 从队列中处理给定的作业
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return void
     *
     * @throws \Throwable
     */
    public function process($connectionName, $job, WorkerOptions $options)
    {
        try {
            // First we will raise the before job event and determine if the job has already run
            // over its maximum attempt limits, which could primarily happen when this job is
            // continually timing out and not actually throwing any exceptions from itself.
			// 首先，我们将引发before job事件并确定是否作业已经运行。
            $this->raiseBeforeJobEvent($connectionName, $job);

            $this->markJobAsFailedIfAlreadyExceedsMaxAttempts(
                $connectionName, $job, (int) $options->maxTries
            );

            if ($job->isDeleted()) {
                return $this->raiseAfterJobEvent($connectionName, $job);
            }

            // Here we will fire off the job and let it process. We will catch any exceptions, so
            // they can be reported to the developer's logs, etc. Once the job is finished the
            // proper events will be fired to let any listeners know this job has completed.
			// 在这里，我们将停止工作并让它进行处理。我们将捕获任何异常。
            $job->fire();

            $this->raiseAfterJobEvent($connectionName, $job);
        } catch (Throwable $e) {
            $this->handleJobException($connectionName, $job, $options, $e);
        }
    }

    /**
     * Handle an exception that occurred while the job was running.
	 * 处理作业运行时发生的异常
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @param  \Throwable  $e
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleJobException($connectionName, $job, WorkerOptions $options, Throwable $e)
    {
        try {
            // First, we will go ahead and mark the job as failed if it will exceed the maximum
            // attempts it is allowed to run the next time we process it. If so we will just
            // go ahead and mark it as failed now so we do not have to release this again.
			// 首先，我们将继续并将该任务标记为失败。
            if (! $job->hasFailed()) {
                $this->markJobAsFailedIfWillExceedMaxAttempts(
                    $connectionName, $job, (int) $options->maxTries, $e
                );

                $this->markJobAsFailedIfWillExceedMaxExceptions(
                    $connectionName, $job, $e
                );
            }

            $this->raiseExceptionOccurredJobEvent(
                $connectionName, $job, $e
            );
        } finally {
            // If we catch an exception, we will attempt to release the job back onto the queue
            // so it is not lost entirely. This'll let the job be retried at a later time by
            // another listener (or this same one). We will re-throw this exception after.
			// 如果我们捕捉到异常，我们将尝试将作业释放回队列。
            if (! $job->isDeleted() && ! $job->isReleased() && ! $job->hasFailed()) {
                $job->release($this->calculateBackoff($job, $options));

                $this->events->dispatch(new JobReleasedAfterException(
                    $connectionName, $job
                ));
            }
        }

        throw $e;
    }

    /**
     * Mark the given job as failed if it has exceeded the maximum allowed attempts.
	 * 如果给定的作业超过了允许的最大尝试次数，则将其标记为失败。
     *
     * This will likely be because the job previously exceeded a timeout.
	 * 这可能是因为作业之前超过了超时时间
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  int  $maxTries
     * @return void
     *
     * @throws \Throwable
     */
    protected function markJobAsFailedIfAlreadyExceedsMaxAttempts($connectionName, $job, $maxTries)
    {
        $maxTries = ! is_null($job->maxTries()) ? $job->maxTries() : $maxTries;

        $retryUntil = $job->retryUntil();

        if ($retryUntil && Carbon::now()->getTimestamp() <= $retryUntil) {
            return;
        }

        if (! $retryUntil && ($maxTries === 0 || $job->attempts() <= $maxTries)) {
            return;
        }

        $this->failJob($job, $e = $this->maxAttemptsExceededException($job));

        throw $e;
    }

    /**
     * Mark the given job as failed if it has exceeded the maximum allowed attempts.
	 * 如果给定的作业超过了允许的最大尝试次数，则将其标记为失败。
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  int  $maxTries
     * @param  \Throwable  $e
     * @return void
     */
    protected function markJobAsFailedIfWillExceedMaxAttempts($connectionName, $job, $maxTries, Throwable $e)
    {
        $maxTries = ! is_null($job->maxTries()) ? $job->maxTries() : $maxTries;

        if ($job->retryUntil() && $job->retryUntil() <= Carbon::now()->getTimestamp()) {
            $this->failJob($job, $e);
        }

        if (! $job->retryUntil() && $maxTries > 0 && $job->attempts() >= $maxTries) {
            $this->failJob($job, $e);
        }
    }

    /**
     * Mark the given job as failed if it has exceeded the maximum allowed attempts.
	 * 如果给定的作业超过了允许的最大尝试次数，则将其标记为失败。
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Throwable  $e
     * @return void
     */
    protected function markJobAsFailedIfWillExceedMaxExceptions($connectionName, $job, Throwable $e)
    {
        if (! $this->cache || is_null($uuid = $job->uuid()) ||
            is_null($maxExceptions = $job->maxExceptions())) {
            return;
        }

        if (! $this->cache->get('job-exceptions:'.$uuid)) {
            $this->cache->put('job-exceptions:'.$uuid, 0, Carbon::now()->addDay());
        }

        if ($maxExceptions <= $this->cache->increment('job-exceptions:'.$uuid)) {
            $this->cache->forget('job-exceptions:'.$uuid);

            $this->failJob($job, $e);
        }
    }

    /**
     * Mark the given job as failed if it should fail on timeouts.
	 * 如果给定的作业在超时时失败，则将其标记为失败。
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Throwable  $e
     * @return void
     */
    protected function markJobAsFailedIfItShouldFailOnTimeout($connectionName, $job, Throwable $e)
    {
        if (method_exists($job, 'shouldFailOnTimeout') ? $job->shouldFailOnTimeout() : false) {
            $this->failJob($job, $e);
        }
    }

    /**
     * Mark the given job as failed and raise the relevant event.
	 * 将给定的作业标记为失败，并引发相关事件。
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Throwable  $e
     * @return void
     */
    protected function failJob($job, Throwable $e)
    {
        $job->fail($e);
    }

    /**
     * Calculate the backoff for the given job.
	 * 计算给定工作的退量
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return int
     */
    protected function calculateBackoff($job, WorkerOptions $options)
    {
        $backoff = explode(
            ',',
            method_exists($job, 'backoff') && ! is_null($job->backoff())
                        ? $job->backoff()
                        : $options->backoff
        );

        return (int) ($backoff[$job->attempts() - 1] ?? last($backoff));
    }

    /**
     * Raise the before queue job event.
	 * 引发before队列作业事件
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @return void
     */
    protected function raiseBeforeJobEvent($connectionName, $job)
    {
        $this->events->dispatch(new JobProcessing(
            $connectionName, $job
        ));
    }

    /**
     * Raise the after queue job event.
	 * 引发队列后作业事件
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @return void
     */
    protected function raiseAfterJobEvent($connectionName, $job)
    {
        $this->events->dispatch(new JobProcessed(
            $connectionName, $job
        ));
    }

    /**
     * Raise the exception occurred queue job event.
	 * 引发异常发生的队列作业事件
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Throwable  $e
     * @return void
     */
    protected function raiseExceptionOccurredJobEvent($connectionName, $job, Throwable $e)
    {
        $this->events->dispatch(new JobExceptionOccurred(
            $connectionName, $job, $e
        ));
    }

    /**
     * Determine if the queue worker should restart.
	 * 确定队列工作线程是否应该重新启动
     *
     * @param  int|null  $lastRestart
     * @return bool
     */
    protected function queueShouldRestart($lastRestart)
    {
        return $this->getTimestampOfLastQueueRestart() != $lastRestart;
    }

    /**
     * Get the last queue restart timestamp, or null.
	 * 获取最后一次队列重启时间戳，或null。
     *
     * @return int|null
     */
    protected function getTimestampOfLastQueueRestart()
    {
        if ($this->cache) {
            return $this->cache->get('illuminate:queue:restart');
        }
    }

    /**
     * Enable async signals for the process.
	 * 为进程启用异步信号
     *
     * @return void
     */
    protected function listenForSignals()
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGQUIT, fn () => $this->shouldQuit = true);
        pcntl_signal(SIGTERM, fn () => $this->shouldQuit = true);
        pcntl_signal(SIGUSR2, fn () => $this->paused = true);
        pcntl_signal(SIGCONT, fn () => $this->paused = false);
    }

    /**
     * Determine if "async" signals are supported.
	 * 确定是否支持"async"信号
     *
     * @return bool
     */
    protected function supportsAsyncSignals()
    {
        return extension_loaded('pcntl');
    }

    /**
     * Determine if the memory limit has been exceeded.
	 * 确定是否已超过内存限制
     *
     * @param  int  $memoryLimit
     * @return bool
     */
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * Stop listening and bail out of the script.
	 * 别再听了，跳出剧本。
     *
     * @param  int  $status
     * @param  WorkerOptions|null  $options
     * @return int
     */
    public function stop($status = 0, $options = null)
    {
        $this->events->dispatch(new WorkerStopping($status, $options));

        return $status;
    }

    /**
     * Kill the process.
	 * 结束进程
     *
     * @param  int  $status
     * @param  \Illuminate\Queue\WorkerOptions|null  $options
     * @return never
     */
    public function kill($status = 0, $options = null)
    {
        $this->events->dispatch(new WorkerStopping($status, $options));

        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }

        exit($status);
    }

    /**
     * Create an instance of MaxAttemptsExceededException.
	 * 创建MaxAttemptsExceededException实例
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @return \Illuminate\Queue\MaxAttemptsExceededException
     */
    protected function maxAttemptsExceededException($job)
    {
        return new MaxAttemptsExceededException(
            $job->resolveName().' has been attempted too many times or run too long. The job may have previously timed out.'
        );
    }

    /**
     * Sleep the script for a given number of seconds.
	 * 让脚本休眠给定的秒数
     *
     * @param  int|float  $seconds
     * @return void
     */
    public function sleep($seconds)
    {
        if ($seconds < 1) {
            usleep($seconds * 1000000);
        } else {
            sleep($seconds);
        }
    }

    /**
     * Set the cache repository implementation.
	 * 设置缓存存储库实现
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return $this
     */
    public function setCache(CacheContract $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Set the name of the worker.
	 * 设置工作者的名称
     *
     * @param  string  $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Register a callback to be executed to pick jobs.
	 * 注册一个要执行的回调函数来选择作业
     *
     * @param  string  $workerName
     * @param  callable  $callback
     * @return void
     */
    public static function popUsing($workerName, $callback)
    {
        if (is_null($callback)) {
            unset(static::$popCallbacks[$workerName]);
        } else {
            static::$popCallbacks[$workerName] = $callback;
        }
    }

    /**
     * Get the queue manager instance.
	 * 获取队列管理器实例
     *
     * @return \Illuminate\Contracts\Queue\Factory
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set the queue manager instance.
	 * 设置队列管理器实例
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $manager
     * @return void
     */
    public function setManager(QueueManager $manager)
    {
        $this->manager = $manager;
    }
}
