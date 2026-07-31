<?php
/**
 * Illuminate，基础，Http，中间件，裁剪字符串
 */

namespace Illuminate\Foundation\Http\Middleware;

use Closure;

class TrimStrings extends TransformsRequest
{
    /**
     * All of the registered skip callbacks.
	 * 所有注册的跳过回调
     *
     * @var array
     */
    protected static $skipCallbacks = [];

    /**
     * The attributes that should not be trimmed.
	 * 不应该被修剪的属性
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
	 * 处理传入请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach (static::$skipCallbacks as $callback) {
            if ($callback($request)) {
                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }

    /**
     * Transform the given value.
	 * 变换给定的值
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Register a callback that instructs the middleware to be skipped.
	 * 注册一个回调，指示跳过中间件。
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function skipWhen(Closure $callback)
    {
        static::$skipCallbacks[] = $callback;
    }
}
