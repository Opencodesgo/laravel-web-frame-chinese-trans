<?php
/**
 * Spatie，FlareClient，Flare中间件，添加通知人名称
 */

namespace Spatie\FlareClient\FlareMiddleware;

use Spatie\FlareClient\Report;

class AddNotifierName implements FlareMiddleware
{
    public const NOTIFIER_NAME = 'Flare Client';

    public function handle(Report $report, $next)
    {
        $report->notifierName(static::NOTIFIER_NAME);

        return $next($report);
    }
}
