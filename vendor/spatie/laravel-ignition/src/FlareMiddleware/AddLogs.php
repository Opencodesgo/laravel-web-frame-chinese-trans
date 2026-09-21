<?php
/**
 * Spatie，LaravelIgnition，异常，Flare 中间件，添加日志
 */

namespace Spatie\LaravelIgnition\FlareMiddleware;

use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder;

class AddLogs implements FlareMiddleware
{
    protected LogRecorder $logRecorder;

    public function __construct()
    {
        $this->logRecorder = app(LogRecorder::class);
    }

    public function handle(Report $report, $next)
    {
        $report->group('logs', $this->logRecorder->getLogMessages());

        return $next($report);
    }
}
