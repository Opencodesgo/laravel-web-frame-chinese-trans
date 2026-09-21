<?php
/**
 * Spatie，FlareClient，截断，抽象截断策略
 */

namespace Spatie\FlareClient\Truncation;

abstract class AbstractTruncationStrategy implements TruncationStrategy
{
    protected ReportTrimmer $reportTrimmer;

    public function __construct(ReportTrimmer $reportTrimmer)
    {
        $this->reportTrimmer = $reportTrimmer;
    }
}
