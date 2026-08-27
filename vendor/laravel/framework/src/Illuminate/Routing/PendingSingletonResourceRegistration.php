<?php
/**
 * Illuminate，路由，待定的单例资源注册
 */

namespace Illuminate\Routing;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;

class PendingSingletonResourceRegistration
{
    use CreatesRegularExpressionRouteConstraints, Macroable;

    /**
     * The resource registrar.
	 * 资源注册
     *
     * @var \Illuminate\Routing\ResourceRegistrar
     */
    protected $registrar;

    /**
     * The resource name.
	 * 资源名称
     *
     * @var string
     */
    protected $name;

    /**
     * The resource controller.
	 * 资源控制骂
     *
     * @var string
     */
    protected $controller;

    /**
     * The resource options.
	 * 资源选项
     *
     * @var array
     */
    protected $options = [];

    /**
     * The resource's registration status.
	 * 资源注册状态
     *
     * @var bool
     */
    protected $registered = false;

    /**
     * Create a new pending singleton resource registration instance.
	 * 创建一个新的挂起的单例资源注册实例
     *
     * @param  \Illuminate\Routing\ResourceRegistrar  $registrar
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return void
     */
    public function __construct(ResourceRegistrar $registrar, $name, $controller, array $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->registrar = $registrar;
        $this->controller = $controller;
    }

    /**
     * Set the methods the controller should apply to.
	 * 设置控制器应该应用的方法
     *
     * @param  array|string|dynamic  $methods
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function only($methods)
    {
        $this->options['only'] = is_array($methods) ? $methods : func_get_args();

        return $this;
    }

    /**
     * Set the methods the controller should exclude.
	 * 设置控制器应该排除的方法
     *
     * @param  array|string|dynamic  $methods
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function except($methods)
    {
        $this->options['except'] = is_array($methods) ? $methods : func_get_args();

        return $this;
    }

    /**
     * Indicate that the resource should have creation and storage routes.
	 * 指示资源应该有创建和存储路由
     *
     * @return $this
     */
    public function creatable()
    {
        $this->options['creatable'] = true;

        return $this;
    }

    /**
     * Indicate that the resource should have a deletion route.
	 * 指示资源应该有删除路由
     *
     * @return $this
     */
    public function destroyable()
    {
        $this->options['destroyable'] = true;

        return $this;
    }

    /**
     * Set the route names for controller actions.
	 * 设置控制器动作的路由名
     *
     * @param  array|string  $names
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function names($names)
    {
        $this->options['names'] = $names;

        return $this;
    }

    /**
     * Set the route name for a controller action.
	 * 设置控制器动作的路由名
     *
     * @param  string  $method
     * @param  string  $name
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function name($method, $name)
    {
        $this->options['names'][$method] = $name;

        return $this;
    }

    /**
     * Override the route parameter names.
	 * 覆盖路由参数名
     *
     * @param  array|string  $parameters
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function parameters($parameters)
    {
        $this->options['parameters'] = $parameters;

        return $this;
    }

    /**
     * Override a route parameter's name.
	 * 重写路由参数的名称
     *
     * @param  string  $previous
     * @param  string  $new
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function parameter($previous, $new)
    {
        $this->options['parameters'][$previous] = $new;

        return $this;
    }

    /**
     * Add middleware to the resource routes.
	 * 向资源路由中添加中间件
     *
     * @param  mixed  $middleware
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function middleware($middleware)
    {
        $middleware = Arr::wrap($middleware);

        foreach ($middleware as $key => $value) {
            $middleware[$key] = (string) $value;
        }

        $this->options['middleware'] = $middleware;

        return $this;
    }

    /**
     * Specify middleware that should be removed from the resource routes.
	 * 指定应该从资源路由中删除的中间件
     *
     * @param  array|string  $middleware
     * @return $this|array
     */
    public function withoutMiddleware($middleware)
    {
        $this->options['excluded_middleware'] = array_merge(
            (array) ($this->options['excluded_middleware'] ?? []), Arr::wrap($middleware)
        );

        return $this;
    }

    /**
     * Add "where" constraints to the resource routes.
	 * 为资源路由添加"where"约束
     *
     * @param  mixed  $wheres
     * @return \Illuminate\Routing\PendingSingletonResourceRegistration
     */
    public function where($wheres)
    {
        $this->options['wheres'] = $wheres;

        return $this;
    }

    /**
     * Register the singleton resource route.
	 * 注册单例资源路由
     *
     * @return \Illuminate\Routing\RouteCollection
     */
    public function register()
    {
        $this->registered = true;

        return $this->registrar->singleton(
            $this->name, $this->controller, $this->options
        );
    }

    /**
     * Handle the object's destruction.
	 * 处理对象的销毁
     *
     * @return void
     */
    public function __destruct()
    {
        if (! $this->registered) {
            $this->register();
        }
    }
}
