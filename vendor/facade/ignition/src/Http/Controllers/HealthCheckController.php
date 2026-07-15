<?php
/**
 * 门面，Ignition，Http，控制器，健康检查控制器
 */

namespace Facade\Ignition\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class HealthCheckController
{
    public function __invoke()
    {
        return [
            'can_execute_commands' => $this->canExecuteCommands(),
        ];
    }

    protected function canExecuteCommands(): bool
    {
        Artisan::call('help', ['--version']);

        $output = Artisan::output();

        return Str::contains($output, app()->version());
    }
}
