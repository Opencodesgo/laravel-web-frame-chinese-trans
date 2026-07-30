<?php
/**
 * Facade，FlareClient，上下文，控制台上下文检测器
 */

namespace Facade\FlareClient\Context;

class ContextContextDetector implements ContextDetectorInterface
{
    public function detectCurrentContext(): ContextInterface
    {
        if ($this->runningInConsole()) {
            return new ConsoleContext($_SERVER['argv'] ?? []);
        }

        return new RequestContext();
    }

    private function runningInConsole(): bool
    {
        if (isset($_ENV['APP_RUNNING_IN_CONSOLE'])) {
            return $_ENV['APP_RUNNING_IN_CONSOLE'] === 'true';
        }

        if (isset($_ENV['FLARE_FAKE_WEB_REQUEST'])) {
            return false;
        }

        return in_array(php_sapi_name(), ['cli', 'phpdb']);
    }
}
