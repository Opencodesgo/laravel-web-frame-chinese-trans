<?php
/**
 * Illuminate，契约，路由，注册接口
 */

namespace Illuminate\Contracts\Routing;

interface Registrar
{
    /**
     * Register a new GET route with the router.
	 * 注册新的GET路由至路由器中
     *
     * @param  string  $uri
     * @param  array|string|callable  $action
     * @return \Illuminate\Routing\Route
     */
    public function get($uri, $action);

    /**
     * Register a new POST route with the router.
	 * 向路由器注册一个新的POST路由
     *
     * @param  string  $uri
     * @param  array|string|callable  $action
     * @return \Illuminate\Routing\Route
     */
    public function post($uri, $action);

    /**
     * Register a new PUT route with the router.
	 * 向路由器注册一条新的PUT路由
     *
     * @param  string  $uri
     * @param  array|string|callable  $action
     * @return \Illuminate\Routing\Route
     */
    public function put($uri, $action);

    /**
     * Register a new DELETE route with the router.
	 * 向路由器注册一条新的DELETE路由
     *
     * @param  string  $uri
     * @param  array|string|callable  $action
     * @return \Illuminate\Routing\Route
     */
    public function delete($uri, $action);

    /**
     * Register a new PATCH route with the router.
	 * 向路由器注册一条新的PATCH路由
     *
     * @param  string  $uri
     * @param  array|string|callable  $action
     * @return \Illuminate\Routing\Route
     */
    public function patch($uri, $action);

    /**
     * Register a new OPTIONS route with the router.
	 * 向路由器注册一个新的OPTIONS路由
     *
     * @param  string  $uri
     * @param  array|string|callable  $action
     * @return \Illuminate\Routing\Route
     */
    public function options($uri, $action);

    /**
     * Register a new route with the given verbs.
	 * 用给定的动词注册一条新路线
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  array|string|callable  $action
     * @return \Illuminate\Routing\Route
     */
    public function match($methods, $uri, $action);

    /**
     * Route a resource to a controller.
	 * 将资源路由到控制器
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function resource($name, $controller, array $options = []);

    /**
     * Create a route group with shared attributes.
	 * 创建具有共享属性的路由组
     *
     * @param  array  $attributes
     * @param  \Closure|string  $routes
     * @return void
     */
    public function group(array $attributes, $routes);

    /**
     * Substitute the route bindings onto the route.
	 * 将路由绑定替换到路由上
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return \Illuminate\Routing\Route
     */
    public function substituteBindings($route);

    /**
     * Substitute the implicit Eloquent model bindings for the route.
	 * 将隐式Eloquent模型绑定替换为路由
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     */
    public function substituteImplicitBindings($route);
}
