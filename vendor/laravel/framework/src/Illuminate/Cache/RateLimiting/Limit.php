<?php
/**
 * Illuminate，缓存，速率限制，限制
 */

namespace Illuminate\Cache\RateLimiting;

class Limit
{
    /**
     * The rate limit signature key.
	 * 速率限制签名密钥
     *
     * @var mixed
     */
    public $key;

    /**
     * The maximum number of attempts allowed within the given number of minutes.
	 * 给定分钟内允许的最大尝试次数
     *
     * @var int
     */
    public $maxAttempts;

    /**
     * The number of minutes until the rate limit is reset.
	 * 距离速率限制被重置的分钟数
     *
     * @var int
     */
    public $decayMinutes;

    /**
     * The response generator callback.
	 * 响应生成器回调
     *
     * @var callable
     */
    public $responseCallback;

    /**
     * Create a new limit instance.
	 * 创建一个新的限制实例
     *
     * @param  mixed  $key
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return void
     */
    public function __construct($key = '', int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    /**
     * Create a new rate limit.
	 * 创建一个新的速率限制
     *
     * @param  int  $maxAttempts
     * @return static
     */
    public static function perMinute($maxAttempts)
    {
        return new static('', $maxAttempts);
    }

    /**
     * Create a new rate limit using minutes as decay time.
	 * 使用分钟作为衰减时间创建新的速率限制
     *
     * @param  int  $decayMinutes
     * @param  int  $maxAttempts
     * @return static
     */
    public static function perMinutes($decayMinutes, $maxAttempts)
    {
        return new static('', $maxAttempts, $decayMinutes);
    }

    /**
     * Create a new rate limit using hours as decay time.
	 * 使用小时作为衰减时间创建一个新的速率限制
     *
     * @param  int  $maxAttempts
     * @param  int  $decayHours
     * @return static
     */
    public static function perHour($maxAttempts, $decayHours = 1)
    {
        return new static('', $maxAttempts, 60 * $decayHours);
    }

    /**
     * Create a new rate limit using days as decay time.
	 * 使用天数作为衰减时间创建新的速率限制
     *
     * @param  int  $maxAttempts
     * @param  int  $decayDays
     * @return static
     */
    public static function perDay($maxAttempts, $decayDays = 1)
    {
        return new static('', $maxAttempts, 60 * 24 * $decayDays);
    }

    /**
     * Create a new unlimited rate limit.
	 * 创建一个新的无限速率限制
     *
     * @return static
     */
    public static function none()
    {
        return new Unlimited;
    }

    /**
     * Set the key of the rate limit.
	 * 设置速率限制的关键字
     *
     * @param  mixed  $key
     * @return $this
     */
    public function by($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the callback that should generate the response when the limit is exceeded.
	 * 设置当超出限制时应该生成响应的回调
     *
     * @param  callable  $callback
     * @return $this
     */
    public function response(callable $callback)
    {
        $this->responseCallback = $callback;

        return $this;
    }
}
