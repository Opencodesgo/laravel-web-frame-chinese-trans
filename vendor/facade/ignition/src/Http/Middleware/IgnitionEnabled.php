<?php
/**
 * Facade，Ignition，Http，中间件，启用点火
 */

namespace Facade\Ignition\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IgnitionEnabled
{
    /**
     * Handle an incoming request.
	 * 处理传入的请求
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->ignitionEnabled()) {
            abort(404);
        }

        return $next($request);
    }

    protected function ignitionEnabled(): bool
    {
        return config('app.debug');
    }
}
