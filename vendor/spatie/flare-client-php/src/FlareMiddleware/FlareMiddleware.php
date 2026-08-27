<?php
/**
 * Spatie，FlareClient，Flare中间件，Flare Middleware
 */

namespace Spatie\FlareClient\FlareMiddleware;

use Closure;
use Spatie\FlareClient\Report;

interface FlareMiddleware
{
    public function handle(Report $report, Closure $next);
}
