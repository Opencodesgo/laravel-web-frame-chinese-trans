<?php
/**
 * Spatie，LaravelIgnition，Http，中间件，启用了可运行的解决方案
 */

namespace Spatie\LaravelIgnition\Http\Middleware;

use Closure;
use Spatie\LaravelIgnition\Support\RunnableSolutionsGuard;

class RunnableSolutionsEnabled
{
    public function handle($request, Closure $next)
    {
        if (! RunnableSolutionsGuard::check()) {
            abort(404);
        }

        return $next($request);
    }
}
