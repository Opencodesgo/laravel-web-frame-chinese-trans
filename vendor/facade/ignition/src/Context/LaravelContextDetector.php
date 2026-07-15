<?php
/**
 * 门面，Ignition，上下文，Laravel 上下文探测器
 */

namespace Facade\Ignition\Context;

use Facade\FlareClient\Context\ContextDetectorInterface;
use Facade\FlareClient\Context\ContextInterface;
use Illuminate\Http\Request;

class LaravelContextDetector implements ContextDetectorInterface
{
    public function detectCurrentContext(): ContextInterface
    {
        if (app()->runningInConsole()) {
            return new LaravelConsoleContext($_SERVER['argv'] ?? []);
        }

        return new LaravelRequestContext(app(Request::class));
    }
}
