<?php
/**
 * Spatie，LaravelIgnition，异常，Flare 中间件，添加通知人名称
 */

namespace Spatie\LaravelIgnition\FlareMiddleware;

use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class AddNotifierName implements FlareMiddleware
{
    public const NOTIFIER_NAME = 'Laravel Client';

    public function handle(Report $report, $next)
    {
        $report->notifierName(static::NOTIFIER_NAME);

        return $next($report);
    }
}
