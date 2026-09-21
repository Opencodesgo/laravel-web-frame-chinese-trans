<?php
/**
 * Illuminate，路由，待处理资源注册
 */

namespace Illuminate\Routing;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;

class PendingResourceRegistration
{
    use CreatesRegularExpressionRouteConstraints, Macroable;

    /**
     * The resource registrar.
	 * 资源注册者
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
	 * 资源控制器
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
	 * 资源的注册状态
     *
     * @var bool
     */
    protected $registered = false;

    /**
     * Create a new pending resource registration instance.
	 * 创建一个新的挂起的资源注册实例
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
     * @return \Illuminate\Routing\PendingResourceRegistration
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
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function except($methods)
    {
        $this->options['except'] = is_array($methods) ? $methods : func_get_args();

        return $this;
    }

    /**
     * Set the route names for controller actions.
	 * 设置控制器动作的路由名
     *
     * @param  array|string  $names
     * @return \Illuminate\Routing\PendingResourceRegistration
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
     * @return \Illuminate\Routing\PendingResourceRegistration
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
     * @return \Illuminate\Routing\PendingResourceRegistration
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
     * @return \Illuminate\Routing\PendingResourceRegistration
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
     * @return \Illuminate\Routing\PendingResourceRegistration
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
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function where($wheres)
    {
        $this->options['wheres'] = $wheres;

        return $this;
    }

    /**
     * Indicate that the resource routes should have "shallow" nesting.
	 * 指示资源路由应该有"浅"嵌套
     *
     * @param  bool  $shallow
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function shallow($shallow = true)
    {
        $this->options['shallow'] = $shallow;

        return $this;
    }

    /**
     * Define the callable that should be invoked on a missing model exception.
	 * 定义在缺失模型异常时应该调用的可调用对象
     *
     * @param  callable  $callback
     * @return $this
     */
    public function missing($callback)
    {
        $this->options['missing'] = $callback;

        return $this;
    }

    /**
     * Indicate that the resource routes should be scoped using the given binding fields.
	 * 指示资源路由应该使用给定的绑定字段限定范围
     *
     * @param  array  $fields
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function scoped(array $fields = [])
    {
        $this->options['bindingFields'] = $fields;

        return $this;
    }

    /**
     * Define which routes should allow "trashed" models to be retrieved when resolving implicit model bindings.
	 * 定义哪些路由应该允许在解析隐式模型绑定时检索"垃圾"模型
     *
     * @param  array  $methods
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function withTrashed(array $methods = [])
    {
        $this->options['trashed'] = $methods;

        return $this;
    }

    /**
     * Register the resource route.
	 * 注册资源路由
     *
     * @return \Illuminate\Routing\RouteCollection
     */
    public function register()
    {
        $this->registered = true;

        return $this->registrar->register(
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
