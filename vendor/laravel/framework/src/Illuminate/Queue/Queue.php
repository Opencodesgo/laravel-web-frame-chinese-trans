<?php
/**
 * Illuminate，队列，队列抽象类
 */

namespace Illuminate\Queue;

use Closure;
use DateTimeInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Arr;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Str;

abstract class Queue
{
    use InteractsWithTime;

    /**
     * The IoC container instance.
	 * IoC容器实例
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The connection name for the queue.
	 * 队列的连接名称
     *
     * @var string
     */
    protected $connectionName;

    /**
     * Indicates that jobs should be dispatched after all database transactions have committed.
	 * 指示应在所有数据库事务提交后分派作业
     *
     * @return $this
     */
    protected $dispatchAfterCommit;

    /**
     * The create payload callbacks.
	 * 创建有效负载回调
     *
     * @var callable[]
     */
    protected static $createPayloadCallbacks = [];

    /**
     * Push a new job onto the queue.
	 * 将新作业推送到队列中
     *
     * @param  string  $queue
     * @param  string  $job
     * @param  mixed  $data
     * @return mixed
     */
    public function pushOn($queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }

    /**
     * Push a new job onto the queue after a delay.
	 * 在延迟后将新作业推入队列
     *
     * @param  string  $queue
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed  $data
     * @return mixed
     */
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }

    /**
     * Push an array of jobs onto the queue.
	 * 将一组作业推入队列
     *
     * @param  array  $jobs
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return void
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }

    /**
     * Create a payload string from the given job and data.
	 * 根据给定的作业和数据创建有效负载字符串
     *
     * @param  \Closure|string|object  $job
     * @param  string  $queue
     * @param  mixed  $data
     * @return string
     *
     * @throws \Illuminate\Queue\InvalidPayloadException
     */
    protected function createPayload($job, $queue, $data = '')
    {
        if ($job instanceof Closure) {
            $job = CallQueuedClosure::create($job);
        }

        $payload = json_encode($this->createPayloadArray($job, $queue, $data), \JSON_UNESCAPED_UNICODE);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidPayloadException(
                'Unable to JSON encode payload. Error code: '.json_last_error()
            );
        }

        return $payload;
    }

    /**
     * Create a payload array from the given job and data.
	 * 根据给定的作业和数据创建有效负载数组
     *
     * @param  string|object  $job
     * @param  string  $queue
     * @param  mixed  $data
     * @return array
     */
    protected function createPayloadArray($job, $queue, $data = '')
    {
        return is_object($job)
                    ? $this->createObjectPayload($job, $queue)
                    : $this->createStringPayload($job, $queue, $data);
    }

    /**
     * Create a payload for an object-based queue handler.
	 * 为基于对象的队列处理程序创建有效负载
     *
     * @param  object  $job
     * @param  string  $queue
     * @return array
     */
    protected function createObjectPayload($job, $queue)
    {
        $payload = $this->withCreatePayloadHooks($queue, [
            'uuid' => (string) Str::uuid(),
            'displayName' => $this->getDisplayName($job),
            'job' => 'Illuminate\Queue\CallQueuedHandler@call',
            'maxTries' => $job->tries ?? null,
            'maxExceptions' => $job->maxExceptions ?? null,
            'failOnTimeout' => $job->failOnTimeout ?? false,
            'backoff' => $this->getJobBackoff($job),
            'timeout' => $job->timeout ?? null,
            'retryUntil' => $this->getJobExpiration($job),
            'data' => [
                'commandName' => $job,
                'command' => $job,
            ],
        ]);

        $command = $this->jobShouldBeEncrypted($job) && $this->container->bound(Encrypter::class)
                    ? $this->container[Encrypter::class]->encrypt(serialize(clone $job))
                    : serialize(clone $job);

        return array_merge($payload, [
            'data' => array_merge($payload['data'], [
                'commandName' => get_class($job),
                'command' => $command,
            ]),
        ]);
    }

    /**
     * Get the display name for the given job.
	 * 得到给定作业的显示名称
     *
     * @param  object  $job
     * @return string
     */
    protected function getDisplayName($job)
    {
        return method_exists($job, 'displayName')
                        ? $job->displayName() : get_class($job);
    }

    /**
     * Get the backoff for an object-based queue handler.
	 * 得到基于对象的队列处理程序的后退
     *
     * @param  mixed  $job
     * @return mixed
     */
    public function getJobBackoff($job)
    {
        if (! method_exists($job, 'backoff') && ! isset($job->backoff)) {
            return;
        }

        if (is_null($backoff = $job->backoff ?? $job->backoff())) {
            return;
        }

        return collect(Arr::wrap($backoff))
            ->map(function ($backoff) {
                return $backoff instanceof DateTimeInterface
                                ? $this->secondsUntil($backoff) : $backoff;
            })->implode(',');
    }

    /**
     * Get the expiration timestamp for an object-based queue handler.
	 * 得到基于对象的队列处理程序的过期时间戳
     *
     * @param  mixed  $job
     * @return mixed
     */
    public function getJobExpiration($job)
    {
        if (! method_exists($job, 'retryUntil') && ! isset($job->retryUntil)) {
            return;
        }

        $expiration = $job->retryUntil ?? $job->retryUntil();

        return $expiration instanceof DateTimeInterface
                        ? $expiration->getTimestamp() : $expiration;
    }

    /**
     * Determine if the job should be encrypted.
	 * 确定是否应该加密作业
     *
     * @param  object  $job
     * @return bool
     */
    protected function jobShouldBeEncrypted($job)
    {
        if ($job instanceof ShouldBeEncrypted) {
            return true;
        }

        return isset($job->shouldBeEncrypted) && $job->shouldBeEncrypted;
    }

    /**
     * Create a typical, string based queue payload array.
	 * 创建一个典型的、基于字符串的队列有效负载数组。
     *
     * @param  string  $job
     * @param  string  $queue
     * @param  mixed  $data
     * @return array
     */
    protected function createStringPayload($job, $queue, $data)
    {
        return $this->withCreatePayloadHooks($queue, [
            'uuid' => (string) Str::uuid(),
            'displayName' => is_string($job) ? explode('@', $job)[0] : null,
            'job' => $job,
            'maxTries' => null,
            'maxExceptions' => null,
            'failOnTimeout' => false,
            'backoff' => null,
            'timeout' => null,
            'data' => $data,
        ]);
    }

    /**
     * Register a callback to be executed when creating job payloads.
	 * 注册一个回调，以便在创建作业有效负载时执行。
     *
     * @param  callable|null  $callback
     * @return void
     */
    public static function createPayloadUsing($callback)
    {
        if (is_null($callback)) {
            static::$createPayloadCallbacks = [];
        } else {
            static::$createPayloadCallbacks[] = $callback;
        }
    }

    /**
     * Create the given payload using any registered payload hooks.
	 * 使用任何已注册的有效负载钩子创建给定的有效负载
     *
     * @param  string  $queue
     * @param  array  $payload
     * @return array
     */
    protected function withCreatePayloadHooks($queue, array $payload)
    {
        if (! empty(static::$createPayloadCallbacks)) {
            foreach (static::$createPayloadCallbacks as $callback) {
                $payload = array_merge($payload, call_user_func(
                    $callback, $this->getConnectionName(), $queue, $payload
                ));
            }
        }

        return $payload;
    }

    /**
     * Enqueue a job using the given callback.
	 * 使用给定的回调为作业排队
     *
     * @param  \Closure|string|object  $job
     * @param  string  $payload
     * @param  string  $queue
     * @param  \DateTimeInterface|\DateInterval|int|null  $delay
     * @param  callable  $callback
     * @return mixed
     */
    protected function enqueueUsing($job, $payload, $queue, $delay, $callback)
    {
        if ($this->shouldDispatchAfterCommit($job) &&
            $this->container->bound('db.transactions')) {
            return $this->container->make('db.transactions')->addCallback(
                function () use ($payload, $queue, $delay, $callback, $job) {
                    return tap($callback($payload, $queue, $delay), function ($jobId) use ($job) {
                        $this->raiseJobQueuedEvent($jobId, $job);
                    });
                }
            );
        }

        return tap($callback($payload, $queue, $delay), function ($jobId) use ($job) {
            $this->raiseJobQueuedEvent($jobId, $job);
        });
    }

    /**
     * Determine if the job should be dispatched after all database transactions have committed.
	 * 确定是否应该在所有数据库事务提交后分派作业
     *
     * @param  \Closure|string|object  $job
     * @return bool
     */
    protected function shouldDispatchAfterCommit($job)
    {
        if (is_object($job) && isset($job->afterCommit)) {
            return $job->afterCommit;
        }

        if (isset($this->dispatchAfterCommit)) {
            return $this->dispatchAfterCommit;
        }

        return false;
    }

    /**
     * Raise the job queued event.
	 * 引发作业排队事件
     *
     * @param  string|int|null  $jobId
     * @param  \Closure|string|object  $job
     * @return void
     */
    protected function raiseJobQueuedEvent($jobId, $job)
    {
        if ($this->container->bound('events')) {
            $this->container['events']->dispatch(new JobQueued($this->connectionName, $jobId, $job));
        }
    }

    /**
     * Get the connection name for the queue.
	 * 得到队列的连接名称
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set the connection name for the queue.
	 * 设置队列的连接名称
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnectionName($name)
    {
        $this->connectionName = $name;

        return $this;
    }

    /**
     * Get the container instance being used by the connection.
	 * 得到连接正在使用的容器实例
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
	 * 设置IoC容器实例
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
