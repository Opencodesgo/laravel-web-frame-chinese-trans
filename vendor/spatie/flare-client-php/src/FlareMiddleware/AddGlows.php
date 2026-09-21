<?php
/**
 * Spatie，FlareClient，Flare中间件，添加 Glows
 */

namespace Spatie\FlareClient\FlareMiddleware;

namespace Spatie\FlareClient\FlareMiddleware;

use Closure;
use Spatie\FlareClient\Glows\GlowRecorder;
use Spatie\FlareClient\Report;

class AddGlows implements FlareMiddleware
{
    protected GlowRecorder $recorder;

    public function __construct(GlowRecorder $recorder)
    {
        $this->recorder = $recorder;
    }

    public function handle(Report $report, Closure $next)
    {
        foreach ($this->recorder->glows() as $glow) {
            $report->addGlow($glow);
        }

        return $next($report);
    }
}
