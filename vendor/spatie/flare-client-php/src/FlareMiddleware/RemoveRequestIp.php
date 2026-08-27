<?php
/**
 * Spatie，FlareClient，Flare中间件，删除请求 Ip
 */

namespace Spatie\FlareClient\FlareMiddleware;

use Spatie\FlareClient\Report;

class RemoveRequestIp implements FlareMiddleware
{
    public function handle(Report $report, $next)
    {
        $context = $report->allContext();

        $context['request']['ip'] = null;

        $report->userProvidedContext($context);

        return $next($report);
    }
}
