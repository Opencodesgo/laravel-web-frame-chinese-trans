<?php
/**
 * Illuminate，路由，控制器，中间件
 */

namespace Illuminate\Routing\Controllers;

use Closure;
use Illuminate\Support\Arr;

class Middleware
{
    /**
     * The middleware that should be assigned.
	 * 应该分配的中间件
     *
     * @var \Closure|string|array
     */
    public $middleware;

    /**
     * The controller methods the middleware should only apply to.
	 * 中间件应该只应用于控制器方法
     *
     * @var array|null
     */
    public $only;

    /**
     * The controller methods the middleware should not apply to.
	 * 中间件不应该应用的控制器方法
     *
     * @var array|null
     */
    public $except;

    /**
     * Create a new controller middleware definition.
	 * 创建一个新的控制器中间件定义
     *
     * @param  \Closure|string|array  $middleware
     * @return void
     */
    public function __construct(Closure|string|array $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Specify the only controller methods the middleware should apply to.
	 * 指定中间件应该应用的唯一控制器方法
     *
     * @param  array|string  $only
     * @return $this
     */
    public function only(array|string $only)
    {
        $this->only = Arr::wrap($only);

        return $this;
    }

    /**
     * Specify the controller methods the middleware should not apply to.
	 * 指定中间件不应用于的控制器方法
     *
     * @param  array|string  $only
     * @return $this
     */
    public function except(array|string $except)
    {
        $this->except = Arr::wrap($except);

        return $this;
    }
}
