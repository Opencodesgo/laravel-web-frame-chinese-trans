<?php
/**
 * Illuminate，队列，中间件，节流阀例外
 */

namespace Illuminate\Queue\Middleware;

use Illuminate\Cache\RateLimiter;
use Illuminate\Container\Container;
use Throwable;

class ThrottlesExceptions
{
    /**
     * The developer specified key that the rate limiter should use.
	 * 开发人员指定的速率限制器应该使用的键
     *
     * @var string
     */
    protected $key;

    /**
     * Indicates whether the throttle key should use the job's UUID.
	 * 指示油门键是否应该使用作业的UUID
     *
     * @var bool
     */
    protected $byJob = false;

    /**
     * The maximum number of attempts allowed before rate limiting applies.
	 * 速率限制生效前允许的最大尝试次数
     *
     * @var int
     */
    protected $maxAttempts;

    /**
     * The number of minutes until the maximum attempts are reset.
	 * 重置最大尝试数之前的分钟数
     *
     * @var int
     */
    protected $decayMinutes;

    /**
     * The number of minutes to wait before retrying the job after an exception.
	 * 在发生异常后重新尝试作业之前等待的分钟数
     *
     * @var int
     */
    protected $retryAfterMinutes = 0;

    /**
     * The callback that determines if rate limiting should apply.
	 * 确定是否应用速率限制的回调
     *
     * @var callable
     */
    protected $whenCallback;

    /**
     * The prefix of the rate limiter key.
	 * 速率限制键的前缀
     *
     * @var string
     */
    protected $prefix = 'laravel_throttles_exceptions:';

    /**
     * The rate limiter instance.
	 * 速率限制器实例
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new middleware instance.
	 * 创建新的中间件实例
     *
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @param  string  $key
     * @return void
     */
    public function __construct($maxAttempts = 10, $decayMinutes = 10)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    /**
     * Process the job.
	 * 处理作业
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        $this->limiter = Container::getInstance()->make(RateLimiter::class);

        if ($this->limiter->tooManyAttempts($jobKey = $this->getKey($job), $this->maxAttempts)) {
            return $job->release($this->getTimeUntilNextRetry($jobKey));
        }

        try {
            $next($job);

            $this->limiter->clear($jobKey);
        } catch (Throwable $throwable) {
            if ($this->whenCallback && ! call_user_func($this->whenCallback, $throwable)) {
                throw $throwable;
            }

            $this->limiter->hit($jobKey, $this->decayMinutes * 60);

            return $job->release($this->retryAfterMinutes * 60);
        }
    }

    /**
     * Specify a callback that should determine if rate limiting behavior should apply.
	 * 指定一个回调函数，以确定是否应用速率限制行为。
     *
     * @param  callable  $callback
     * @return $this
     */
    public function when(callable $callback)
    {
        $this->whenCallback = $callback;

        return $this;
    }

    /**
     * Set the prefix of the rate limiter key.
	 * 设置速率限制键的前缀
     *
     * @param  string  $prefix
     * @return $this
     */
    public function withPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Specify the number of minutes a job should be delayed when it is released (before it has reached its max exceptions).
	 * 指定作业在释放时（在达到最大异常之前）应该延迟的分钟数
     *
     * @param  int  $backoff
     * @return $this
     */
    public function backoff($backoff)
    {
        $this->retryAfterMinutes = $backoff;

        return $this;
    }

    /**
     * Get the cache key associated for the rate limiter.
	 * 得到与速率限制器相关联的缓存键
     *
     * @param  mixed  $job
     * @return string
     */
    protected function getKey($job)
    {
        if ($this->key) {
            return $this->prefix.$this->key;
        } elseif ($this->byJob) {
            return $this->prefix.$job->job->uuid();
        }

        return $this->prefix.md5(get_class($job));
    }

    /**
     * Set the value that the rate limiter should be keyed by.
	 * 设置速率限制器的键值
     *
     * @param  string  $key
     * @return $this
     */
    public function by($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Indicate that the throttle key should use the job's UUID.
	 * 指示油门键应该使用作业的UUID
     *
     * @return $this
     */
    public function byJob()
    {
        $this->byJob = true;

        return $this;
    }

    /**
     * Get the number of seconds that should elapse before the job is retried.
	 * 得到在重试作业之前应该经过的秒数
     *
     * @param  string  $key
     * @return int
     */
    protected function getTimeUntilNextRetry($key)
    {
        return $this->limiter->availableIn($key) + 3;
    }
}
