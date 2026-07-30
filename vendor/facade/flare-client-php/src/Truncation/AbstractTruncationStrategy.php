<?php
/**
 * Facade，FlareClient，截断，摘要截断策略
 */

namespace Facade\FlareClient\Truncation;

abstract class AbstractTruncationStrategy implements TruncationStrategy
{
    /** @var ReportTrimmer */
    protected $reportTrimmer;

    public function __construct(ReportTrimmer $reportTrimmer)
    {
        $this->reportTrimmer = $reportTrimmer;
    }
}
