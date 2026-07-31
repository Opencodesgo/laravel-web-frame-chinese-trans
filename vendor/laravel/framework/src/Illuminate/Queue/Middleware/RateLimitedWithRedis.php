<?php
/**
 * Illuminate，队列，中间件，Redis的速率限制
 */

namespace Illuminate\Queue\Middleware;

use Illuminate\Container\Container;
use Illuminate\Contracts\Redis\Factory as Redis;
use Illuminate\Redis\Limiters\DurationLimiter;
use Illuminate\Support\InteractsWithTime;

class RateLimitedWithRedis extends RateLimited
{
    use InteractsWithTime;

    /**
     * The Redis factory implementation.
	 * Redis工厂实现
     *
     * @var \Illuminate\Contracts\Redis\Factory
     */
    protected $redis;

    /**
     * The timestamp of the end of the current duration by key.
	 * 按键表示当前持续时间结束的时间戳
     *
     * @var array
     */
    public $decaysAt = [];

    /**
     * Create a new middleware instance.
	 * 创建新的中间件实例
     *
     * @param  string  $limiterName
     * @return void
     */
    public function __construct($limiterName)
    {
        parent::__construct($limiterName);

        $this->redis = Container::getInstance()->make(Redis::class);
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
            if ($this->tooManyAttempts($limit->key, $limit->maxAttempts, $limit->decayMinutes)) {
                return $this->shouldRelease
                    ? $job->release($this->getTimeUntilNextRetry($limit->key))
                    : false;
            }
        }

        return $next($job);
    }

    /**
     * Determine if the given key has been "accessed" too many times.
	 * 确定给定的键是否被"访问"了太多次
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return bool
     */
    protected function tooManyAttempts($key, $maxAttempts, $decayMinutes)
    {
        $limiter = new DurationLimiter(
            $this->redis, $key, $maxAttempts, $decayMinutes * 60
        );

        return tap(! $limiter->acquire(), function () use ($key, $limiter) {
            $this->decaysAt[$key] = $limiter->decaysAt;
        });
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
        return ($this->decaysAt[$key] - $this->currentTime()) + 3;
    }

    /**
     * Prepare the object after unserialization.
	 * 反序列化后准备对象
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();

        $this->redis = Container::getInstance()->make(Redis::class);
    }
}
