<?php
/**
 * Illuminate，基础，异常，注册错误视图路径
 */

namespace Illuminate\Foundation\Exceptions;

use Illuminate\Support\Facades\View;

class RegisterErrorViewPaths
{
    /**
     * Register the error view paths.
	 * 注册错误视图路径
     *
     * @return void
     */
    public function __invoke()
    {
        View::replaceNamespace('errors', collect(config('view.paths'))->map(function ($path) {
            return "{$path}/errors";
        })->push(__DIR__.'/views')->all());
    }
}
