<?php
/**
 * 门面，Ignition，中间件，自定义分组
 */

namespace Facade\Ignition\Middleware;

use Facade\FlareClient\Enums\GroupingTypes;
use Facade\FlareClient\Report;

class CustomizeGrouping
{
    protected $groupingType;

    public function __construct($groupingType)
    {
        $this->groupingType = $groupingType;
    }

    public function handle(Report $report, $next)
    {
        $report->groupByTopFrame();

        if ($this->groupingType === GroupingTypes::EXCEPTION) {
            $report->groupByException();
        }

        return $next($report);
    }
}
