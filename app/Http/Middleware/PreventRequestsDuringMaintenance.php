<?php
/**
 * App, 基础，Http, 中间件, 维护期间的预防性请求
 */

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
	 * 在启用维护模式时应该可以访问的URI
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
