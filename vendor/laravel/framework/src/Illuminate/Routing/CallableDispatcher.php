<?php
/**
 * Illuminate，路由，调度调度程序
 */

namespace Illuminate\Routing;

use Illuminate\Container\Container;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use ReflectionFunction;

class CallableDispatcher implements CallableDispatcherContract
{
    use RouteDependencyResolverTrait;

    /**
     * The container instance.
	 * 容器实例
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Create a new callable dispatcher instance.
	 * 创建一个新的可调用调度程序实例
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

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
        return $callable(...array_values($this->resolveParameters($route, $callable)));
    }

    /**
     * Resolve the parameters for the callable.
	 * 解析可调用对象的参数
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  callable  $callable
     * @return array
     */
    protected function resolveParameters(Route $route, $callable)
    {
        return $this->resolveMethodDependencies($route->parametersWithoutNulls(), new ReflectionFunction($callable));
    }
}
