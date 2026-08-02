<?php
/**
 * Illuminate，路由，路由集合接口
 */

namespace Illuminate\Routing;

use Illuminate\Http\Request;

interface RouteCollectionInterface
{
    /**
     * Add a Route instance to the collection.
	 * 向集合添加路由实例
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return \Illuminate\Routing\Route
     */
    public function add(Route $route);

    /**
     * Refresh the name look-up table.
	 * 刷新名称查找表
     *
     * This is done in case any names are fluently defined or if routes are overwritten.
	 * 这样做是为了防止任何名称被流利地定义或路由被覆盖。
     *
     * @return void
     */
    public function refreshNameLookups();

    /**
     * Refresh the action look-up table.
	 * 刷新操作查找表
     *
     * This is done in case any actions are overwritten with new controllers.
	 * 这样做是为了防止任何操作被新控制器覆盖
     *
     * @return void
     */
    public function refreshActionLookups();

    /**
     * Find the first route matching a given request.
	 * 找到与给定请求匹配的第一条路由
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Routing\Route
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function match(Request $request);

    /**
     * Get routes from the collection by method.
	 * 通过方法从集合中获取路由
     *
     * @param  string|null  $method
     * @return \Illuminate\Routing\Route[]
     */
    public function get($method = null);

    /**
     * Determine if the route collection contains a given named route.
	 * 确定路由集合是否包含给定的命名路由
     *
     * @param  string  $name
     * @return bool
     */
    public function hasNamedRoute($name);

    /**
     * Get a route instance by its name.
	 * 通过名称获取路由实例
     *
     * @param  string  $name
     * @return \Illuminate\Routing\Route|null
     */
    public function getByName($name);

    /**
     * Get a route instance by its controller action.
	 * 通过它的控制器动作获取一个路由实例
     *
     * @param  string  $action
     * @return \Illuminate\Routing\Route|null
     */
    public function getByAction($action);

    /**
     * Get all of the routes in the collection.
	 * 获取集合中的所有路由
     *
     * @return \Illuminate\Routing\Route[]
     */
    public function getRoutes();

    /**
     * Get all of the routes keyed by their HTTP verb / method.
	 * 获取所有由HTTP动词/方法指定的路由
     *
     * @return array
     */
    public function getRoutesByMethod();

    /**
     * Get all of the routes keyed by their name.
	 * 通过名称获取所有路由
     *
     * @return \Illuminate\Routing\Route[]
     */
    public function getRoutesByName();
}
