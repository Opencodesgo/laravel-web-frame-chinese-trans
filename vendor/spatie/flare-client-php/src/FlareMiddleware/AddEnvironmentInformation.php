<?php
/**
 * Spatie，FlareClient，Flare中间件，添加环境令牌
 */

namespace Spatie\FlareClient\FlareMiddleware;

use Closure;
use Spatie\FlareClient\Report;

class AddEnvironmentInformation implements FlareMiddleware
{
    public function handle(Report $report, Closure $next)
    {
        $report->group('env', [
            'php_version' => phpversion(),
        ]);

        return $next($report);
    }
}
