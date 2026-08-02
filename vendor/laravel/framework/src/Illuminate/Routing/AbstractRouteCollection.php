<?php
/**
 * Illuminate，路由，抽象路由集合
 */

namespace Illuminate\Routing;

use ArrayIterator;
use Countable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use IteratorAggregate;
use LogicException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

abstract class AbstractRouteCollection implements Countable, IteratorAggregate, RouteCollectionInterface
{
    /**
     * Handle the matched route.
	 * 处理匹配的路由
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Routing\Route|null  $route
     * @return \Illuminate\Routing\Route
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function handleMatchedRoute(Request $request, $route)
    {
        if (! is_null($route)) {
            return $route->bind($request);
        }

        // If no route was found we will now check if a matching route is specified by
        // another HTTP verb. If it is we will need to throw a MethodNotAllowed and
        // inform the user agent of which HTTP verb it should use for this route.
		// 如果没有找到路由，我们将检查是否指定了匹配指定了的另外HTTP动作的路由。
		// 如果是，我们需要抛出一个methodnotalallowed并通知用户代理应该为该路由使用哪个HTTP动词。
        $others = $this->checkForAlternateVerbs($request);

        if (count($others) > 0) {
            return $this->getRouteForMethods($request, $others);
        }

        throw new NotFoundHttpException;
    }

    /**
     * Determine if any routes match on another HTTP verb.
	 * 确定是否有任何路由与另一个HTTP动词匹配。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function checkForAlternateVerbs($request)
    {
        $methods = array_diff(Router::$verbs, [$request->getMethod()]);

        // Here we will spin through all verbs except for the current request verb and
        // check to see if any routes respond to them. If they do, we will return a
        // proper error response with the correct headers on the response string.
		// 这里我们将遍历除当前请求动作外并检查是否有任何路由响应它们。
		// 如果是，我们将返回正确的错误响应，在响应字符串上有正确的报头。
        return array_values(array_filter(
            $methods,
            function ($method) use ($request) {
                return ! is_null($this->matchAgainstRoutes($this->get($method), $request, false));
            }
        ));
    }

    /**
     * Determine if a route in the array matches the request.
	 * 确定数组中的路由是否与请求匹配
     *
     * @param  \Illuminate\Routing\Route[]  $routes
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $includingMethod
     * @return \Illuminate\Routing\Route|null
     */
    protected function matchAgainstRoutes(array $routes, $request, $includingMethod = true)
    {
        [$fallbacks, $routes] = collect($routes)->partition(function ($route) {
            return $route->isFallback;
        });

        return $routes->merge($fallbacks)->first(function (Route $route) use ($request, $includingMethod) {
			//  Illuminate\Routing\Route，public function matches(Request $request, $includingMethod = true)
            return $route->matches($request, $includingMethod);
        });
    }

    /**
     * Get a route (if necessary) that responds when other available methods are present.
	 * 获取一个路由（如果有必要），当存在其他可用方法时，它会做出响应。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string[]  $methods
     * @return \Illuminate\Routing\Route
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function getRouteForMethods($request, array $methods)
    {
        if ($request->method() === 'OPTIONS') {
            return (new Route('OPTIONS', $request->path(), function () use ($methods) {
                return new Response('', 200, ['Allow' => implode(',', $methods)]);
            }))->bind($request);
        }

        $this->methodNotAllowed($methods, $request->method());
    }

    /**
     * Throw a method not allowed HTTP exception.
	 * 抛出一个方法不允许HTTP异常
     *
     * @param  array  $others
     * @param  string  $method
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function methodNotAllowed(array $others, $method)
    {
        throw new MethodNotAllowedHttpException(
            $others,
            sprintf(
                'The %s method is not supported for this route. Supported methods: %s.',
                $method,
                implode(', ', $others)
            )
        );
    }

    /**
     * Compile the routes for caching.
	 * 编译用于缓存的路由
     *
     * @return array
     */
    public function compile()
    {
        $compiled = $this->dumper()->getCompiledRoutes();

        $attributes = [];

        foreach ($this->getRoutes() as $route) {
            $attributes[$route->getName()] = [
                'methods' => $route->methods(),
                'uri' => $route->uri(),
                'action' => $route->getAction(),
                'fallback' => $route->isFallback,
                'defaults' => $route->defaults,
                'wheres' => $route->wheres,
                'bindingFields' => $route->bindingFields(),
                'lockSeconds' => $route->locksFor(),
                'waitSeconds' => $route->waitsFor(),
            ];
        }

        return compact('compiled', 'attributes');
    }

    /**
     * Return the CompiledUrlMatcherDumper instance for the route collection.
	 * 返回路由集合的CompiledUrlMatcherDumper实例
     *
     * @return \Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper
     */
    public function dumper()
    {
        return new CompiledUrlMatcherDumper($this->toSymfonyRouteCollection());
    }

    /**
     * Convert the collection to a Symfony RouteCollection instance.
	 * 将集合转换为Symfony RouteCollection实例
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function toSymfonyRouteCollection()
    {
        $symfonyRoutes = new SymfonyRouteCollection;

        $routes = $this->getRoutes();

        foreach ($routes as $route) {
            if (! $route->isFallback) {
                $symfonyRoutes = $this->addToSymfonyRoutesCollection($symfonyRoutes, $route);
            }
        }

        foreach ($routes as $route) {
            if ($route->isFallback) {
                $symfonyRoutes = $this->addToSymfonyRoutesCollection($symfonyRoutes, $route);
            }
        }

        return $symfonyRoutes;
    }

    /**
     * Add a route to the SymfonyRouteCollection instance.
	 * 向SymfonyRouteCollection实例添加一条路由
     *
     * @param  \Symfony\Component\Routing\RouteCollection  $symfonyRoutes
     * @param  \Illuminate\Routing\Route  $route
     * @return \Symfony\Component\Routing\RouteCollection
     */
    protected function addToSymfonyRoutesCollection(SymfonyRouteCollection $symfonyRoutes, Route $route)
    {
        $name = $route->getName();

        if (Str::endsWith($name, '.') &&
            ! is_null($symfonyRoutes->get($name))) {
            $name = null;
        }

        if (! $name) {
            $route->name($name = $this->generateRouteName());

            $this->add($route);
        } elseif (! is_null($symfonyRoutes->get($name))) {
            throw new LogicException("Unable to prepare route [{$route->uri}] for serialization. Another route has already been assigned name [{$name}].");
        }

        $symfonyRoutes->add($route->getName(), $route->toSymfonyRoute());

        return $symfonyRoutes;
    }

    /**
     * Get a randomly generated route name.
	 * 获取随机生成的路由名称
     *
     * @return string
     */
    protected function generateRouteName()
    {
        return 'generated::'.Str::random();
    }

    /**
     * Get an iterator for the items.
	 * 获取项的迭代器
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getRoutes());
    }

    /**
     * Count the number of items in the collection.
	 * 计算集合中的项数
     *
     * @return int
     */
    public function count()
    {
        return count($this->getRoutes());
    }
}
