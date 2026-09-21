<?php
/**
 * Spatie，LaravelIgnition，Http，控制器，更新配置控制器
 */

namespace Spatie\LaravelIgnition\Http\Controllers;

use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\LaravelIgnition\Http\Requests\UpdateConfigRequest;

class UpdateConfigController
{
    public function __invoke(UpdateConfigRequest $request)
    {
        $result = (new IgnitionConfig())->saveValues($request->validated());

        return response()->json($result);
    }
}
