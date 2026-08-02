<?php
/**
 * Illuminate，路由，已编译的路由集合
 */

namespace Illuminate\Routing;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\RequestContext;

class CompiledRouteCollection extends AbstractRouteCollection
{
    /**
     * The compiled routes collection.
	 * 编译的路由集合
     *
     * @var array
     */
    protected $compiled = [];

    /**
     * An array of the route attributes keyed by name.
	 * 按名称键值的路由属性数组
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The dynamically added routes that were added after loading the cached, compiled routes.
	 * 加载缓存的编译路由后添加的动态添加的路由
     *
     * @var \Illuminate\Routing\RouteCollection|null
     */
    protected $routes;

    /**
     * The router instance used by the route.
	 * 被路由使用的路由器实例
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The container instance used by the route.
	 * 被路由使用的容器实例
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Create a new CompiledRouteCollection instance.
	 * 创建新的已编译路由集合实例
     *
     * @param  array  $compiled
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $compiled, array $attributes)
    {
        $this->compiled = $compiled;
        $this->attributes = $attributes;
        $this->routes = new RouteCollection;
    }

    /**
     * Add a Route instance to the collection.
	 * 添加路由实例至集合
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return \Illuminate\Routing\Route
     */
    public function add(Route $route)
    {
        return $this->routes->add($route);
    }

    /**
     * Refresh the name look-up table.
	 * 刷新名称查找表
     *
     * This is done in case any names are fluently defined or if routes are overwritten.
	 * 这样做是为了防止任何名称被流利地定义或路由被覆盖。
     *
     * @return void
     */
    public function refreshNameLookups()
    {
        //
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
        //
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
        $matcher = new CompiledUrlMatcher(
            $this->compiled, (new RequestContext)->fromRequest(
                $trimmedRequest = $this->requestWithoutTrailingSlash($request)
            )
        );

        $route = null;

        try {
			// Symfony\Component\Routing\Matcher\UrlMatcher
            if ($result = $matcher->matchRequest($trimmedRequest)) {
                $route = $this->getByName($result['_route']);
            }
        } catch (ResourceNotFoundException|MethodNotAllowedException $e) {
            try {
                return $this->routes->match($request);
            } catch (NotFoundHttpException $e) {
                //
            }
        }

        if ($route && $route->isFallback) {
            try {
                $dynamicRoute = $this->routes->match($request);

                if (! $dynamicRoute->isFallback) {
                    $route = $dynamicRoute;
                }
            } catch (NotFoundHttpException|MethodNotAllowedHttpException $e) {
                //
            }
        }

        return $this->handleMatchedRoute($request, $route);
    }

    /**
     * Get a cloned instance of the given request without any trailing slash on the URI.
	 * 获取给定请求的克隆实例，在URI上不带任何斜杠。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Request
     */
    protected function requestWithoutTrailingSlash(Request $request)
    {
        $trimmedRequest = Request::createFromBase($request);

        $parts = explode('?', $request->server->get('REQUEST_URI'), 2);

        $trimmedRequest->server->set(
            'REQUEST_URI', rtrim($parts[0], '/').(isset($parts[1]) ? '?'.$parts[1] : '')
        );

        return $trimmedRequest;
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
        return $this->getRoutesByMethod()[$method] ?? [];
    }

    /**
     * Determine if the route collection contains a given named route.
	 * 确定路由集合是否包含给定的路由名称
     *
     * @param  string  $name
     * @return bool
     */
    public function hasNamedRoute($name)
    {
		// 先从属性中找再找名称中找
        return isset($this->attributes[$name]) || $this->routes->hasNamedRoute($name);
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
        if (isset($this->attributes[$name])) {
            return $this->newRoute($this->attributes[$name]);
        }

        return $this->routes->getByName($name);
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
        $attributes = collect($this->attributes)->first(function (array $attributes) use ($action) {
            if (isset($attributes['action']['controller'])) {
                return trim($attributes['action']['controller'], '\\') === $action;
            }

            return $attributes['action']['uses'] === $action;
        });

        if ($attributes) {
            return $this->newRoute($attributes);
        }

        return $this->routes->getByAction($action);
    }

    /**
     * Get all of the routes in the collection.
	 * 获取集合中的所有路由
     *
     * @return \Illuminate\Routing\Route[]
     */
    public function getRoutes()
    {
        return collect($this->attributes)
            ->map(function (array $attributes) {
                return $this->newRoute($attributes);
            })
            ->merge($this->routes->getRoutes())
            ->values()
            ->all();
    }

    /**
     * Get all of the routes keyed by their HTTP verb / method.
	 * 获取所有由HTTP动词/方法指定的路由
     *
     * @return array
     */
    public function getRoutesByMethod()
    {
        return collect($this->getRoutes())
            ->groupBy(function (Route $route) {
                return $route->methods();
            })
            ->map(function (Collection $routes) {
                return $routes->mapWithKeys(function (Route $route) {
                    return [$route->uri => $route];
                })->all();
            })
            ->all();
    }

    /**
     * Get all of the routes keyed by their name.
	 * 把所有的路由按名字标记
     *
     * @return \Illuminate\Routing\Route[]
     */
    public function getRoutesByName()
    {
        return collect($this->getRoutes())
            ->keyBy(function (Route $route) {
                return $route->getName();
            })
            ->all();
    }

    /**
     * Resolve an array of attributes to a Route instance.
	 * 将属性数组解析为路由实例
     *
     * @param  array  $attributes
     * @return \Illuminate\Routing\Route
     */
    protected function newRoute(array $attributes)
    {
        if (empty($attributes['action']['prefix'] ?? '')) {
            $baseUri = $attributes['uri'];
        } else {
            $prefix = trim($attributes['action']['prefix'], '/');

            $baseUri = trim(implode(
                '/', array_slice(
                    explode('/', trim($attributes['uri'], '/')),
                    count($prefix !== '' ? explode('/', $prefix) : [])
                )
            ), '/');
        }

        return $this->router->newRoute($attributes['methods'], $baseUri == '' ? '/' : $baseUri, $attributes['action'])
            ->setFallback($attributes['fallback'])
            ->setDefaults($attributes['defaults'])
            ->setWheres($attributes['wheres'])
            ->setBindingFields($attributes['bindingFields'])
            ->block($attributes['lockSeconds'] ?? null, $attributes['waitSeconds'] ?? null);
    }

    /**
     * Set the router instance on the route.
	 * 设置路由上的路由器实例
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Set the container instance on the route.
	 * 在路由上设置容器实例
     *
     * @param  \Illuminate\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}
