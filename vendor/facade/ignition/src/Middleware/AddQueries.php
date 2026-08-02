<?php
/**
 * Facade，Ignition，中间件，添加查询
 */

namespace Facade\Ignition\Middleware;

use Facade\FlareClient\Report;
use Facade\Ignition\QueryRecorder\QueryRecorder;

class AddQueries
{
    /** @var \Facade\Ignition\QueryRecorder\QueryRecorder */
    protected $queryRecorder;

    public function __construct(QueryRecorder $queryRecorder)
    {
        $this->queryRecorder = $queryRecorder;
    }

    public function handle(Report $report, $next)
    {
        $report->group('queries', $this->queryRecorder->getQueries());

        return $next($report);
    }
}
