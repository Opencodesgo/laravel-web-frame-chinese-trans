<?php
/**
 * Spatie，LaravelIgnition，异常，Flare 中间件，添加异常信息
 */

namespace Spatie\LaravelIgnition\FlareMiddleware;

use Illuminate\Database\QueryException;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class AddExceptionInformation implements FlareMiddleware
{
    public function handle(Report $report, $next)
    {
        $throwable = $report->getThrowable();

        $this->addUserDefinedContext($report);

        if (! $throwable instanceof QueryException) {
            return $next($report);
        }

        $report->group('exception', [
            'raw_sql' => $throwable->getSql(),
        ]);

        return $next($report);
    }

    private function addUserDefinedContext(Report $report): void
    {
        $throwable = $report->getThrowable();

        if ($throwable === null) {
            return;
        }

        if (! method_exists($throwable, 'context')) {
            return;
        }

        $context = $throwable->context();

        if (! is_array($context)) {
            return;
        }

        foreach ($context as $key => $value) {
            $report->context($key, $value);
        }
    }
}
