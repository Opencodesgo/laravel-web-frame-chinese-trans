<?php
/**
 * Illuminate，基础，路由，预知可调用调度程序
 */

namespace Illuminate\Foundation\Routing;

use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;

class PrecognitionCallableDispatcher extends CallableDispatcher
{
    /**
     * Dispatch a request to a given callable.
	 * 将请求分派给给定的可调用对象
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  callable  $callable
     * @return mixed
     */
    public function dispatch(Route $route, $callable)
    {
        $this->resolveParameters($route, $callable);

        abort(204);
    }
}
