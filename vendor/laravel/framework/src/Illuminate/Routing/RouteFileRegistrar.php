<?php
/**
 * Illuminate，路由，路由文件注册者
 */

namespace Illuminate\Routing;

class RouteFileRegistrar
{
    /**
     * The router instance.
	 * 路由器实例
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * Create a new route file registrar instance.
	 * 创建一个新的路由文件注册器实例
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Require the given routes file.
	 * 需要给定的路由文件
     *
     * @param  string  $routes
     * @return void
     */
    public function register($routes)
    {
        $router = $this->router;

        require $routes;
    }
}
