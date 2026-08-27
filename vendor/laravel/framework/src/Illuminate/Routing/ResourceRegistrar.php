<?php
/**
 * Illuminate，路由，资源注册
 */

namespace Illuminate\Routing;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ResourceRegistrar
{
    /**
     * The router instance.
	 * 路由器实例
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The default actions for a resourceful controller.
	 * 资源控制器的默认动作
     *
     * @var string[]
     */
    protected $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    /**
     * The default actions for a singleton resource controller.
	 * 单个资源控制器的默认操作
     *
     * @var string[]
     */
    protected $singletonResourceDefaults = ['show', 'edit', 'update'];

    /**
     * The parameters set for this resource instance.
	 * 为此资源实例设置的参数
     *
     * @var array|string
     */
    protected $parameters;

    /**
     * The global parameter mapping.
	 * 全局参数映射
     *
     * @var array
     */
    protected static $parameterMap = [];

    /**
     * Singular global parameters.
	 * 奇异全局参数
     *
     * @var bool
     */
    protected static $singularParameters = true;

    /**
     * The verbs used in the resource URIs.
	 * 资源uri中使用的动词
     *
     * @var array
     */
    protected static $verbs = [
        'create' => 'create',
        'edit' => 'edit',
    ];

    /**
     * Create a new resource registrar instance.
	 * 创建一个新的资源注册器实例
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Route a resource to a controller.
	 * 将资源路由到控制器
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\RouteCollection
     */
    public function register($name, $controller, array $options = [])
    {
        if (isset($options['parameters']) && ! isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }

        // If the resource name contains a slash, we will assume the developer wishes to
        // register these resource routes with a prefix so we will set that up out of
        // the box so they don't have to mess with it. Otherwise, we will continue.
		// 如果资源名包含斜杠，我们将假定开发人员希望这样去注册这些资源路由。
        if (str_contains($name, '/')) {
            $this->prefixedResource($name, $controller, $options);

            return;
        }

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route parameters, which should be the base resources.
		// 我们需要从资源名中提取基本资源。
        $base = $this->getResourceWildcard(last(explode('.', $name)));

        $defaults = $this->resourceDefaults;

        $collection = new RouteCollection;

        $resourceMethods = $this->getResourceMethods($defaults, $options);

        foreach ($resourceMethods as $m) {
            $route = $this->{'addResource'.ucfirst($m)}(
                $name, $base, $controller, $options
            );

            if (isset($options['bindingFields'])) {
                $this->setResourceBindingFields($route, $options['bindingFields']);
            }

            if (isset($options['trashed']) &&
                in_array($m, ! empty($options['trashed']) ? $options['trashed'] : array_intersect($resourceMethods, ['show', 'edit', 'update']))) {
                $route->withTrashed();
            }

            $collection->add($route);
        }

        return $collection;
    }

    /**
     * Route a singleton resource to a controller.
	 * 将单例资源路由到控制器
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\RouteCollection
     */
    public function singleton($name, $controller, array $options = [])
    {
        if (isset($options['parameters']) && ! isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }

        // If the resource name contains a slash, we will assume the developer wishes to
        // register these singleton routes with a prefix so we will set that up out of
        // the box so they don't have to mess with it. Otherwise, we will continue.
		// 如果资源名包含斜杠，我们将假定开发人员希望用前缀注册这些单例路由。
        if (str_contains($name, '/')) {
            $this->prefixedSingleton($name, $controller, $options);

            return;
        }

        $defaults = $this->singletonResourceDefaults;

        $collection = new RouteCollection;

        $resourceMethods = $this->getResourceMethods($defaults, $options);

        foreach ($resourceMethods as $m) {
            $route = $this->{'addSingleton'.ucfirst($m)}(
                $name, $controller, $options
            );

            if (isset($options['bindingFields'])) {
                $this->setResourceBindingFields($route, $options['bindingFields']);
            }

            $collection->add($route);
        }

        return $collection;
    }

    /**
     * Build a set of prefixed resource routes.
	 * 建立一组前缀资源路由
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return void
     */
    protected function prefixedResource($name, $controller, array $options)
    {
        [$name, $prefix] = $this->getResourcePrefix($name);

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route parameters, which should be the base resources.
		// 我们需要从资源名中提取基本资源。
        $callback = function ($me) use ($name, $controller, $options) {
            $me->resource($name, $controller, $options);
        };

        return $this->router->group(compact('prefix'), $callback);
    }

    /**
     * Build a set of prefixed singleton routes.
	 * 构建一组带前缀的单例路由
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return void
     */
    protected function prefixedSingleton($name, $controller, array $options)
    {
        [$name, $prefix] = $this->getResourcePrefix($name);

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route parameters, which should be the base resources.
		// 我们需要从资源名中提取基本资源。
        $callback = function ($me) use ($name, $controller, $options) {
            $me->singleton($name, $controller, $options);
        };

        return $this->router->group(compact('prefix'), $callback);
    }

    /**
     * Extract the resource and prefix from a resource name.
	 * 从资源名称中提取资源和前缀
     *
     * @param  string  $name
     * @return array
     */
    protected function getResourcePrefix($name)
    {
        $segments = explode('/', $name);

        // To get the prefix, we will take all of the name segments and implode them on
        // a slash. This will generate a proper URI prefix for us. Then we take this
        // last segment, which will be considered the final resources name we use.
		// 为了获得前缀，我们将获取所有名称段并将它们内爆。
        $prefix = implode('/', array_slice($segments, 0, -1));

        return [end($segments), $prefix];
    }

    /**
     * Get the applicable resource methods.
	 * 获取适用的资源方法
     *
     * @param  array  $defaults
     * @param  array  $options
     * @return array
     */
    protected function getResourceMethods($defaults, $options)
    {
        $methods = $defaults;

        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array) $options['only']);
        }

        if (isset($options['except'])) {
            $methods = array_diff($methods, (array) $options['except']);
        }

        if (isset($options['creatable'])) {
            $methods = isset($options['apiSingleton'])
                            ? array_merge(['store', 'destroy'], $methods)
                            : array_merge(['create', 'store', 'destroy'], $methods);

            return $this->getResourceMethods(
                $methods, array_values(Arr::except($options, ['creatable']))
            );
        }

        if (isset($options['destroyable'])) {
            $methods = array_merge(['destroy'], $methods);

            return $this->getResourceMethods(
                $methods, array_values(Arr::except($options, ['destroyable']))
            );
        }

        return $methods;
    }

    /**
     * Add the index method for a resourceful route.
	 * 增加资源路由的索引方法
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceIndex($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'index', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the create method for a resourceful route.
	 * 为资源路由添加create方法
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceCreate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/'.static::$verbs['create'];

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'create', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the store method for a resourceful route.
	 * 为资源路由添加store方法
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceStore($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'store', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the show method for a resourceful route.
	 * 为资源路由增加show方法
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceShow($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'show', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the edit method for a resourceful route.
	 * 添加资源路由的编辑方法
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceEdit($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name).'/{'.$base.'}/'.static::$verbs['edit'];

        $action = $this->getResourceAction($name, $controller, 'edit', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the update method for a resourceful route.
	 * 添加资源路由的更新方式
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceUpdate($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'update', $options);

        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }

    /**
     * Add the destroy method for a resourceful route.
	 * 为资源路由添加destroy方法
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceDestroy($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'destroy', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Add the create method for a singleton route.
	 * 为单例路由添加create方法
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addSingletonCreate($name, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/'.static::$verbs['create'];

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'create', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the store method for a singleton route.
	 * 为单例路由添加store方法
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addSingletonStore($name, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'store', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the show method for a singleton route.
	 * 为单例路由增加show方法
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addSingletonShow($name, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'show', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the edit method for a singleton route.
	 * 为单例路由添加edit方法
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addSingletonEdit($name, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name).'/'.static::$verbs['edit'];

        $action = $this->getResourceAction($name, $controller, 'edit', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the update method for a singleton route.
	 * 为单例路由添加更新方法
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addSingletonUpdate($name, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name);

        $action = $this->getResourceAction($name, $controller, 'update', $options);

        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }

    /**
     * Add the destroy method for a singleton route.
	 * 为单例路由添加destroy方法
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addSingletonDestroy($name, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name);

        $action = $this->getResourceAction($name, $controller, 'destroy', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Get the name for a given resource with shallowness applied when applicable.
	 * 获取给定资源的名称，并在适用时应用浅度。
     *
     * @param  string  $name
     * @param  array  $options
     * @return string
     */
    protected function getShallowName($name, $options)
    {
        return isset($options['shallow']) && $options['shallow']
                    ? last(explode('.', $name))
                    : $name;
    }

    /**
     * Set the route's binding fields if the resource is scoped.
	 * 如果资源有作用域，则设置路由的绑定字段。
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  array  $bindingFields
     * @return void
     */
    protected function setResourceBindingFields($route, $bindingFields)
    {
        preg_match_all('/(?<={).*?(?=})/', $route->uri, $matches);

        $fields = array_fill_keys($matches[0], null);

        $route->setBindingFields(array_replace(
            $fields, array_intersect_key($bindingFields, $fields)
        ));
    }

    /**
     * Get the base resource URI for a given resource.
	 * 获取给定资源的基本资源URI
     *
     * @param  string  $resource
     * @return string
     */
    public function getResourceUri($resource)
    {
        if (! str_contains($resource, '.')) {
            return $resource;
        }

        // Once we have built the base URI, we'll remove the parameter holder for this
        // base resource name so that the individual route adders can suffix these
        // paths however they need to, as some do not have any parameters at all.
		// 一旦我们构建了基本URI，我们将删除它的参数持有者。
        $segments = explode('.', $resource);

        $uri = $this->getNestedResourceUri($segments);

        return str_replace('/{'.$this->getResourceWildcard(end($segments)).'}', '', $uri);
    }

    /**
     * Get the URI for a nested resource segment array.
	 * 获取嵌套资源段数组的URI
     *
     * @param  array  $segments
     * @return string
     */
    protected function getNestedResourceUri(array $segments)
    {
        // We will spin through the segments and create a place-holder for each of the
        // resource segments, as well as the resource itself. Then we should get an
        // entire string for the resource URI that contains all nested resources.
		// 我们将旋转这些片段，为每个资源段，以及资源本身。
        return implode('/', array_map(function ($s) {
            return $s.'/{'.$this->getResourceWildcard($s).'}';
        }, $segments));
    }

    /**
     * Format a resource parameter for usage.
	 * 格式化资源参数以供使用
     *
     * @param  string  $value
     * @return string
     */
    public function getResourceWildcard($value)
    {
        if (isset($this->parameters[$value])) {
            $value = $this->parameters[$value];
        } elseif (isset(static::$parameterMap[$value])) {
            $value = static::$parameterMap[$value];
        } elseif ($this->parameters === 'singular' || static::$singularParameters) {
            $value = Str::singular($value);
        }

        return str_replace('-', '_', $value);
    }

    /**
     * Get the action array for a resource route.
	 * 获取资源路由的操作数组
     *
     * @param  string  $resource
     * @param  string  $controller
     * @param  string  $method
     * @param  array  $options
     * @return array
     */
    protected function getResourceAction($resource, $controller, $method, $options)
    {
        $name = $this->getResourceRouteName($resource, $method, $options);

        $action = ['as' => $name, 'uses' => $controller.'@'.$method];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }

        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }

        if (isset($options['wheres'])) {
            $action['where'] = $options['wheres'];
        }

        if (isset($options['missing'])) {
            $action['missing'] = $options['missing'];
        }

        return $action;
    }

    /**
     * Get the name for a given resource.
	 * 获取给定资源的名称
     *
     * @param  string  $resource
     * @param  string  $method
     * @param  array  $options
     * @return string
     */
    protected function getResourceRouteName($resource, $method, $options)
    {
        $name = $resource;

        // If the names array has been provided to us we will check for an entry in the
        // array first. We will also check for the specific method within this array
        // so the names may be specified on a more "granular" level using methods.
		// 如果names数组已经提供给我们，我们将检查数组中的条目。
        if (isset($options['names'])) {
            if (is_string($options['names'])) {
                $name = $options['names'];
            } elseif (isset($options['names'][$method])) {
                return $options['names'][$method];
            }
        }

        // If a global prefix has been assigned to all names for this resource, we will
        // grab that so we can prepend it onto the name when we create this name for
        // the resource action. Otherwise we'll just use an empty string for here.
		// 如果已为该资源的所有名称分配了全局前缀，则将抓住它，这样我们就可以在创建这个名字的时候把它加到名字上。
        $prefix = isset($options['as']) ? $options['as'].'.' : '';

        return trim(sprintf('%s%s.%s', $prefix, $name, $method), '.');
    }

    /**
     * Set or unset the unmapped global parameters to singular.
	 * 将未映射的全局参数设置或取消设置为单数
     *
     * @param  bool  $singular
     * @return void
     */
    public static function singularParameters($singular = true)
    {
        static::$singularParameters = (bool) $singular;
    }

    /**
     * Get the global parameter map.
	 * 获取全局参数映射
     *
     * @return array
     */
    public static function getParameters()
    {
        return static::$parameterMap;
    }

    /**
     * Set the global parameter mapping.
	 * 设置全局参数映射
     *
     * @param  array  $parameters
     * @return void
     */
    public static function setParameters(array $parameters = [])
    {
        static::$parameterMap = $parameters;
    }

    /**
     * Get or set the action verbs used in the resource URIs.
	 * 获取或设置资源uri中使用的动作动词
     *
     * @param  array  $verbs
     * @return array
     */
    public static function verbs(array $verbs = [])
    {
        if (empty($verbs)) {
            return static::$verbs;
        } else {
            static::$verbs = array_merge(static::$verbs, $verbs);
        }
    }
}
