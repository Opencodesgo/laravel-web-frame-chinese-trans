<?php
/**
 * Illuminate，缓存，速率限制器
 */

namespace Illuminate\Cache;

use Closure;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\InteractsWithTime;

class RateLimiter
{
    use InteractsWithTime;

    /**
     * The cache store implementation.
	 * 缓存存储实现
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * The configured limit object resolvers.
	 * 配置的限制对象解析器
     *
     * @var array
     */
    protected $limiters = [];

    /**
     * Create a new rate limiter instance.
	 * 创建一个新的速率限制器实例
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Register a named limiter configuration.
	 * 注册一个命名的限制器配置
     *
     * @param  string  $name
     * @param  \Closure  $callback
     * @return $this
     */
    public function for(string $name, Closure $callback)
    {
        $this->limiters[$name] = $callback;

        return $this;
    }

    /**
     * Get the given named rate limiter.
	 * 获取给定的命名速率限制器
     *
     * @param  string  $name
     * @return \Closure
     */
    public function limiter(string $name)
    {
        return $this->limiters[$name] ?? null;
    }

    /**
     * Attempts to execute a callback if it's not limited.
	 * 如果没有限制，尝试执行回调。
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  \Closure  $callback
     * @param  int  $decaySeconds
     * @return mixed
     */
    public function attempt($key, $maxAttempts, Closure $callback, $decaySeconds = 60)
    {
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        return tap($callback() ?: true, function () use ($key, $decaySeconds) {
            $this->hit($key, $decaySeconds);
        });
    }

    /**
     * Determine if the given key has been "accessed" too many times.
	 * 确定给定的键是否被"访问"了太多次
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return bool
     */
    public function tooManyAttempts($key, $maxAttempts)
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($this->cleanRateLimiterKey($key).':timer')) {
                return true;
            }

            $this->resetAttempts($key);
        }

        return false;
    }

    /**
     * Increment the counter for a given key for a given decay time.
	 * 为给定的衰减时间增加给定键的计数器
     *
     * @param  string  $key
     * @param  int  $decaySeconds
     * @return int
     */
    public function hit($key, $decaySeconds = 60)
    {
        $key = $this->cleanRateLimiterKey($key);

        $this->cache->add(
            $key.':timer', $this->availableAt($decaySeconds), $decaySeconds
        );

        $added = $this->cache->add($key, 0, $decaySeconds);

        $hits = (int) $this->cache->increment($key);

        if (! $added && $hits == 1) {
            $this->cache->put($key, 1, $decaySeconds);
        }

        return $hits;
    }

    /**
     * Get the number of attempts for the given key.
	 * 获取给定键的尝试次数
     *
     * @param  string  $key
     * @return mixed
     */
    public function attempts($key)
    {
        $key = $this->cleanRateLimiterKey($key);

        return $this->cache->get($key, 0);
    }

    /**
     * Reset the number of attempts for the given key.
	 * 重置给定键的尝试次数
     *
     * @param  string  $key
     * @return mixed
     */
    public function resetAttempts($key)
    {
        $key = $this->cleanRateLimiterKey($key);

        return $this->cache->forget($key);
    }

    /**
     * Get the number of retries left for the given key.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return int
     */
    public function remaining($key, $maxAttempts)
    {
        $key = $this->cleanRateLimiterKey($key);

        $attempts = $this->attempts($key);

        return $maxAttempts - $attempts;
    }

    /**
     * Get the number of retries left for the given key.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return int
     */
    public function retriesLeft($key, $maxAttempts)
    {
        return $this->remaining($key, $maxAttempts);
    }

    /**
     * Clear the hits and lockout timer for the given key.
	 * 清除给定键的命中和锁定计时器
     *
     * @param  string  $key
     * @return void
     */
    public function clear($key)
    {
        $key = $this->cleanRateLimiterKey($key);

        $this->resetAttempts($key);

        $this->cache->forget($key.':timer');
    }

    /**
     * Get the number of seconds until the "key" is accessible again.
	 * 获取"密钥"再次可访问之前的秒数
     *
     * @param  string  $key
     * @return int
     */
    public function availableIn($key)
    {
        $key = $this->cleanRateLimiterKey($key);

        return max(0, $this->cache->get($key.':timer') - $this->currentTime());
    }

    /**
     * Clean the rate limiter key from unicode characters.
	 * 从unicode字符中清除速率限制键
     *
     * @param  string  $key
     * @return string
     */
    public function cleanRateLimiterKey($key)
    {
        return preg_replace('/&([a-z])[a-z]+;/i', '$1', htmlentities($key));
    }
}
