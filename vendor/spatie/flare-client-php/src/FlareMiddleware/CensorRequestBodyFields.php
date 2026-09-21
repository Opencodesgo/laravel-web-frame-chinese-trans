<?php
/**
 * Spatie，FlareClient，Flare中间件，审查请求正文字段
 */

namespace Spatie\FlareClient\FlareMiddleware;

use Spatie\FlareClient\Report;

class CensorRequestBodyFields implements FlareMiddleware
{
    protected array $fieldNames = [];

    public function __construct(array $fieldNames)
    {
        $this->fieldNames = $fieldNames;
    }

    public function handle(Report $report, $next)
    {
        $context = $report->allContext();

        foreach ($this->fieldNames as $fieldName) {
            if (isset($context['request_data']['body'][$fieldName])) {
                $context['request_data']['body'][$fieldName] = '<CENSORED>';
            }
        }

        $report->userProvidedContext($context);

        return $next($report);
    }
}
