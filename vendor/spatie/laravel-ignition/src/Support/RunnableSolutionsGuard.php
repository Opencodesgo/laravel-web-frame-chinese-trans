<?php
/**
 * Spatie，LaravelIgnition，支持，可运行的解决方案保护
 */

namespace Spatie\LaravelIgnition\Support;

class RunnableSolutionsGuard
{
    /**
     * Check if runnable solutions are allowed based on the current
     * environment and config.
	 * 检查是否允许基于当前的环境和配置可运行解决方案
     *
     * @return bool
     */
    public static function check(): bool
    {
        if (! config('app.debug')) {
            // Never run solutions in when debug mode is not enabled.
			// 不要在未启用调试模式时运行解决方案。

            return false;
        }

        if (config('ignition.enable_runnable_solutions') !== null) {
            // Allow enabling or disabling runnable solutions regardless of environment
            // if the IGNITION_ENABLE_RUNNABLE_SOLUTIONS env var is explicitly set.

            return config('ignition.enable_runnable_solutions');
        }

        if (! app()->environment('local') && ! app()->environment('development')) {
            // Never run solutions on non-local environments. This avoids exposing
            // applications that are somehow APP_ENV=production with APP_DEBUG=true.
			// 不要在非本地环境中运行解决方案。

            return false;
        }

        return config('app.debug');
    }
}
