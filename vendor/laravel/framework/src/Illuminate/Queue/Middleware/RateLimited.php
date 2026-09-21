<?php
/**
 * Illuminate，队列，中间件，限速
 */

namespace Illuminate\Queue\Middleware;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;

class RateLimited
{
    /**
     * The rate limiter instance.
	 * 速率限制器实例
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * The name of the rate limiter.
	 * 速率限制器的名称
     *
     * @var string
     */
    protected $limiterName;

    /**
     * Indicates if the job should be released if the limit is exceeded.
	 * 指示在超过限制时是否应该释放作业
     *
     * @var bool
     */
    public $shouldRelease = true;

    /**
     * Create a new middleware instance.
	 * 创建一个新的中间件实例
     *
     * @param  string  $limiterName
     * @return void
     */
    public function __construct($limiterName)
    {
        $this->limiter = Container::getInstance()->make(RateLimiter::class);

        $this->limiterName = $limiterName;
    }

    /**
     * Process the job.
	 * 作业过程
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        if (is_null($limiter = $this->limiter->limiter($this->limiterName))) {
            return $next($job);
        }

        $limiterResponse = $limiter($job);

        if ($limiterResponse instanceof Unlimited) {
            return $next($job);
        }

        return $this->handleJob(
            $job,
            $next,
            collect(Arr::wrap($limiterResponse))->map(function ($limit) {
                return (object) [
                    'key' => md5($this->limiterName.$limit->key),
                    'maxAttempts' => $limit->maxAttempts,
                    'decayMinutes' => $limit->decayMinutes,
                ];
            })->all()
        );
    }

    /**
     * Handle a rate limited job.
	 * 处理速率受限的作业
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @param  array  $limits
     * @return mixed
     */
    protected function handleJob($job, $next, array $limits)
    {
        foreach ($limits as $limit) {
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                return $this->shouldRelease
                        ? $job->release($this->getTimeUntilNextRetry($limit->key))
                        : false;
            }

            $this->limiter->hit($limit->key, $limit->decayMinutes * 60);
        }

        return $next($job);
    }

    /**
     * Do not release the job back to the queue if the limit is exceeded.
	 * 如果超过了限制，不要将作业释放回队列。
     *
     * @return $this
     */
    public function dontRelease()
    {
        $this->shouldRelease = false;

        return $this;
    }

    /**
     * Get the number of seconds that should elapse before the job is retried.
	 * 获取在重试作业之前应该经过的秒数
     *
     * @param  string  $key
     * @return int
     */
    protected function getTimeUntilNextRetry($key)
    {
        return $this->limiter->availableIn($key) + 3;
    }

    /**
     * Prepare the object for serialization.
	 * 为序列化准备对象
     *
     * @return array
     */
    public function __sleep()
    {
        return [
            'limiterName',
            'shouldRelease',
        ];
    }

    /**
     * Prepare the object after unserialization.
	 * 反序列化后准备对象
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->limiter = Container::getInstance()->make(RateLimiter::class);
    }
}
