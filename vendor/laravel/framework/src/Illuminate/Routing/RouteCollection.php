<?php
/**
 * Illuminate，路由，路由集合
 */

namespace Illuminate\Routing;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RouteCollection extends AbstractRouteCollection
{
    /**
     * An array of the routes keyed by method.
	 * 方法键值的路由数组
     *
     * @var array
     */
    protected $routes = [];

    /**
     * A flattened array of all of the routes.
	 * 所有路线的平面化排列
     *
     * @var \Illuminate\Routing\Route[]
     */
    protected $allRoutes = [];

    /**
     * A look-up table of routes by their names.
	 * 按名称查找路由的表
     *
     * @var \Illuminate\Routing\Route[]
     */
    protected $nameList = [];

    /**
     * A look-up table of routes by controller action.
	 * 根据控制器动作的路由查找表
     *
     * @var \Illuminate\Routing\Route[]
     */
    protected $actionList = [];

    /**
     * Add a Route instance to the collection.
	 * 向集合添加一个Route实例
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return \Illuminate\Routing\Route
     */
    public function add(Route $route)
    {
        $this->addToCollections($route);

        $this->addLookups($route);

        return $route;
    }

    /**
     * Add the given route to the arrays of routes.
	 * 将给定的路由添加到路由数组中
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     */
    protected function addToCollections($route)
    {
        $domainAndUri = $route->getDomain().$route->uri();

        foreach ($route->methods() as $method) {
            $this->routes[$method][$domainAndUri] = $route;
        }

        $this->allRoutes[$method.$domainAndUri] = $route;
    }

    /**
     * Add the route to any look-up tables if necessary.
	 * 如有必要，将该路由添加到任何查找表中。
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     */
    protected function addLookups($route)
    {
        // If the route has a name, we will add it to the name look-up table so that we
        // will quickly be able to find any route associate with a name and not have
        // to iterate through every route every time we need to perform a look-up.
		// 如果路由有名称，我们将把它添加到名称查找表中。
        if ($name = $route->getName()) {
            $this->nameList[$name] = $route;
        }

        // When the route is routing to a controller we will also store the action that
        // is used by the route. This will let us reverse route to controllers while
        // processing a request and easily generate URLs to the given controllers.
		// 当路由路由到控制器时，我们也会存储被路线所使用的动作。
        $action = $route->getAction();

        if (isset($action['controller'])) {
            $this->addToActionList($action, $route);
        }
    }

    /**
     * Add a route to the controller action dictionary.
	 * 添加到控制器动作字典的路由
     *
     * @param  array  $action
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     */
    protected function addToActionList($action, $route)
    {
        $this->actionList[trim($action['controller'], '\\')] = $route;
    }

    /**
     * Refresh the name look-up table.
	 * 刷新名称查找表
     *
     * This is done in case any names are fluently defined or if routes are overwritten.
	 * 这样做是为了防止任何名称被流利地定义或路由被覆盖
     *
     * @return void
     */
    public function refreshNameLookups()
    {
        $this->nameList = [];

        foreach ($this->allRoutes as $route) {
            if ($route->getName()) {
                $this->nameList[$route->getName()] = $route;
            }
        }
    }

    /**
     * Refresh the action look-up table.
	 * 刷新操作查找表
     *
     * This is done in case any actions are overwritten with new controllers.
	 * 这样做是为了防止任何操作被新控制器覆盖
     *
     * @return void
     */
    public function refreshActionLookups()
    {
        $this->actionList = [];

        foreach ($this->allRoutes as $route) {
            if (isset($route->getAction()['controller'])) {
                $this->addToActionList($route->getAction(), $route);
            }
        }
    }

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
    public function match(Request $request)
    {
        $routes = $this->get($request->getMethod());

        // First, we will see if we can find a matching route for this current request
        // method. If we can, great, we can just return it so that it can be called
        // by the consumer. Otherwise we will check for routes with another verb.
		// 首先，我们将查看是否可以为当前请求方法找到匹配的路由。
        $route = $this->matchAgainstRoutes($routes, $request);

        return $this->handleMatchedRoute($request, $route);
    }

    /**
     * Get routes from the collection by method.
	 * 通过方法从集合中获取路由
     *
     * @param  string|null  $method
     * @return \Illuminate\Routing\Route[]
     */
    public function get($method = null)
    {
        return is_null($method) ? $this->getRoutes() : Arr::get($this->routes, $method, []);
    }

    /**
     * Determine if the route collection contains a given named route.
	 * 确定路由集合是否包含给定的命名路由
     *
     * @param  string  $name
     * @return bool
     */
    public function hasNamedRoute($name)
    {
        return ! is_null($this->getByName($name));
    }

    /**
     * Get a route instance by its name.
	 * 通过名称获取路由实例
     *
     * @param  string  $name
     * @return \Illuminate\Routing\Route|null
     */
    public function getByName($name)
    {
        return $this->nameList[$name] ?? null;
    }

    /**
     * Get a route instance by its controller action.
	 * 通过它的控制器动作获取一个路由实例
     *
     * @param  string  $action
     * @return \Illuminate\Routing\Route|null
     */
    public function getByAction($action)
    {
        return $this->actionList[$action] ?? null;
    }

    /**
     * Get all of the routes in the collection.
	 * 获取集合中的所有路由
     *
     * @return \Illuminate\Routing\Route[]
     */
    public function getRoutes()
    {
        return array_values($this->allRoutes);
    }

    /**
     * Get all of the routes keyed by their HTTP verb / method.
	 * 获取所有由HTTP动词/方法指定的路由
     *
     * @return array
     */
    public function getRoutesByMethod()
    {
        return $this->routes;
    }

    /**
     * Get all of the routes keyed by their name.
	 * 把所有的路线按名字标记
     *
     * @return \Illuminate\Routing\Route[]
     */
    public function getRoutesByName()
    {
        return $this->nameList;
    }

    /**
     * Convert the collection to a Symfony RouteCollection instance.
	 * 将集合转换为Symfony RouteCollection实例
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function toSymfonyRouteCollection()
    {
        $symfonyRoutes = parent::toSymfonyRouteCollection();

        $this->refreshNameLookups();

        return $symfonyRoutes;
    }

    /**
     * Convert the collection to a CompiledRouteCollection instance.
	 * 将集合转换为CompiledRouteCollection实例
     *
     * @param  \Illuminate\Routing\Router  $router
     * @param  \Illuminate\Container\Container  $container
     * @return \Illuminate\Routing\CompiledRouteCollection
     */
    public function toCompiledRouteCollection(Router $router, Container $container)
    {
        ['compiled' => $compiled, 'attributes' => $attributes] = $this->compile();

        return (new CompiledRouteCollection($compiled, $attributes))
            ->setRouter($router)
            ->setContainer($container);
    }
}
