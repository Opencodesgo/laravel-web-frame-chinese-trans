<?php
/**
 * Illuminate, 基础, Http, 中间件, 将空字符串转换为NULL
 */

namespace Illuminate\Foundation\Http\Middleware;

use Closure;

class ConvertEmptyStringsToNull extends TransformsRequest
{
    /**
     * All of the registered skip callbacks.
	 * 所有注册的跳过回调
     *
     * @var array
     */
    protected static $skipCallbacks = [];

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
        return $value === '' ? null : $value;
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
