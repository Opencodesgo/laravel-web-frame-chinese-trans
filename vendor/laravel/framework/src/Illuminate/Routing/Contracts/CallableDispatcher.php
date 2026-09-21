<?php
/**
 * Illuminate，路由，契约，调用调度程序
 */

namespace Illuminate\Routing\Contracts;

use Illuminate\Routing\Route;

interface CallableDispatcher
{
    /**
     * Dispatch a request to a given callable.
	 * 将请求分派给给定的可调用对象
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  callable  $callable
     * @return mixed
     */
    public function dispatch(Route $route, $callable);
}
