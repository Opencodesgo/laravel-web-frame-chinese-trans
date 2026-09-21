<?php
/**
 * Illuminate，路由，路由
 */

namespace Illuminate\Routing;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Matching\HostValidator;
use Illuminate\Routing\Matching\MethodValidator;
use Illuminate\Routing\Matching\SchemeValidator;
use Illuminate\Routing\Matching\UriValidator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Laravel\SerializableClosure\SerializableClosure;
use LogicException;
use Symfony\Component\Routing\Route as SymfonyRoute;

class Route
{
    use CreatesRegularExpressionRouteConstraints, Macroable, RouteDependencyResolverTrait;

    /**
     * The URI pattern the route responds to.
	 * 路由响应的URI模式
     *
     * @var string
     */
    public $uri;

    /**
     * The HTTP methods the route responds to.
	 * 路由响应的HTTP方法
     *
     * @var array
     */
    public $methods;

    /**
     * The route action array.
	 * 路由动作数组
     *
     * @var array
     */
    public $action;

    /**
     * Indicates whether the route is a fallback route.
	 * 是否为回退路由
     *
     * @var bool
     */
    public $isFallback = false;

    /**
     * The controller instance.
	 * 控制器实例
     *
     * @var mixed
     */
    public $controller;

    /**
     * The default values for the route.
	 * 路由默认值 
     *
     * @var array
     */
    public $defaults = [];

    /**
     * The regular expression requirements.
	 * 正则表达式要求
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The array of matched parameters.
	 * 匹配参数的数组
     *
     * @var array|null
     */
    public $parameters;

    /**
     * The parameter names for the route.
	 * 路由的参数名
     *
     * @var array|null
     */
    public $parameterNames;

    /**
     * The array of the matched parameters' original values.
	 * 匹配参数的原始值的数组
     *
     * @var array
     */
    protected $originalParameters;

    /**
     * Indicates "trashed" models can be retrieved when resolving implicit model bindings for this route.
	 * 表示在解析此路由的隐式模型绑定时可以检索“被丢弃”的模型
     *
     * @var bool
     */
    protected $withTrashedBindings = false;

    /**
     * Indicates the maximum number of seconds the route should acquire a session lock for.
	 * 路由获得会话锁定的最大秒数
     *
     * @var int|null
     */
    protected $lockSeconds;

    /**
     * Indicates the maximum number of seconds the route should wait while attempting to acquire a session lock.
	 * 指示路由在尝试获取会话锁时应等待的最大秒数
     *
     * @var int|null
     */
    protected $waitSeconds;

    /**
     * The computed gathered middleware.
	 * 计算集合中间件
     *
     * @var array|null
     */
    public $computedMiddleware;

    /**
     * The compiled version of the route.
	 * 路由的编译版本
     *
     * @var \Symfony\Component\Routing\CompiledRoute
     */
    public $compiled;

    /**
     * The router instance used by the route.
	 * 路由使用的路由器实例
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The container instance used by the route.
	 * 路由使用的容器实例
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The fields that implicit binding should use for a given parameter.
	 * 隐式绑定应该为给定参数使用的字段
     *
     * @var array
     */
    protected $bindingFields = [];

    /**
     * The validators used by the routes.
	 * 路由使用的验证器
     *
     * @var array
     */
    public static $validators;

    /**
     * Create a new Route instance.
	 * 创建一个新的Route实例
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  \Closure|array  $action
     * @return void
     */
    public function __construct($methods, $uri, $action)
    {
        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = Arr::except($this->parseAction($action), ['prefix']);

        if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }

        $this->prefix(is_array($action) ? Arr::get($action, 'prefix') : '');
    }

    /**
     * Parse the route action into a standard array.
	 * 将路由操作解析成一个标准数组
     *
     * @param  callable|array|null  $action
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    protected function parseAction($action)
    {
        return RouteAction::parse($this->uri, $action);
    }

    /**
     * Run the route action and return the response.
	 * 运行路由操作并返回响应
     *
     * @return mixed
     */
    public function run()
    {
        $this->container = $this->container ?: new Container;

        try {
            if ($this->isControllerAction()) {
                return $this->runController();
            }

            return $this->runCallable();
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Checks whether the route's action is a controller.
	 * 检查路由的动作是否为控制器
     *
     * @return bool
     */
    protected function isControllerAction()
    {
        return is_string($this->action['uses']) && ! $this->isSerializedClosure();
    }

    /**
     * Run the route action and return the response.
	 * 运行路由操作并返回响应
     *
     * @return mixed
     */
    protected function runCallable()
    {
        $callable = $this->action['uses'];

        if ($this->isSerializedClosure()) {
            $callable = unserialize($this->action['uses'])->getClosure();
        }

        return $this->container[CallableDispatcher::class]->dispatch($this, $callable);
    }

    /**
     * Determine if the route action is a serialized Closure.
	 * 确定路由操作是否是一个序列化的闭包
     *
     * @return bool
     */
    protected function isSerializedClosure()
    {
        return RouteAction::containsSerializedClosure($this->action);
    }

    /**
     * Run the route action and return the response.
	 * 运行路由操作并返回响应
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function runController()
    {
        return $this->controllerDispatcher()->dispatch(
            $this, $this->getController(), $this->getControllerMethod()
        );
    }

    /**
     * Get the controller instance for the route.
	 * 获取路由的控制器实例
     *
     * @return mixed
     */
    public function getController()
    {
        if (! $this->controller) {
            $class = $this->getControllerClass();

            $this->controller = $this->container->make(ltrim($class, '\\'));
        }

        return $this->controller;
    }

    /**
     * Get the controller class used for the route.
	 * 获取用于路由的控制器类
     *
     * @return string
     */
    public function getControllerClass()
    {
        return $this->parseControllerCallback()[0];
    }

    /**
     * Get the controller method used for the route.
	 * 获取用于路由的控制器方法
     *
     * @return string
     */
    protected function getControllerMethod()
    {
        return $this->parseControllerCallback()[1];
    }

    /**
     * Parse the controller.
	 * 解析控制器
     *
     * @return array
     */
    protected function parseControllerCallback()
    {
        return Str::parseCallback($this->action['uses']);
    }

    /**
     * Flush the cached container instance on the route.
	 * 刷新路由上缓存的容器实例
     *
     * @return void
     */
    public function flushController()
    {
        $this->computedMiddleware = null;
        $this->controller = null;
    }

    /**
     * Determine if the route matches a given request.
	 * 确定路由是否与给定请求匹配
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $includingMethod
     * @return bool
     */
    public function matches(Request $request, $includingMethod = true)
    {
        $this->compileRoute();

        foreach (self::getValidators() as $validator) {
            if (! $includingMethod && $validator instanceof MethodValidator) {
                continue;
            }

            if (! $validator->matches($this, $request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compile the route into a Symfony CompiledRoute instance.
	 * 将路由编译成一个Symfony CompiledRoute实例
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    protected function compileRoute()
    {
        if (! $this->compiled) {
            $this->compiled = $this->toSymfonyRoute()->compile();
        }

        return $this->compiled;
    }

    /**
     * Bind the route to a given request for execution.
	 * 将路由绑定到给定的执行请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function bind(Request $request)
    {
        $this->compileRoute();

        $this->parameters = (new RouteParameterBinder($this))
                        ->parameters($request);

        $this->originalParameters = $this->parameters;

        return $this;
    }

    /**
     * Determine if the route has parameters.
	 * 确定路由是否有参数
     *
     * @return bool
     */
    public function hasParameters()
    {
        return isset($this->parameters);
    }

    /**
     * Determine a given parameter exists from the route.
	 * 从路由中确定给定参数是否存在
     *
     * @param  string  $name
     * @return bool
     */
    public function hasParameter($name)
    {
        if ($this->hasParameters()) {
            return array_key_exists($name, $this->parameters());
        }

        return false;
    }

    /**
     * Get a given parameter from the route.
	 * 从路由中获取给定的参数
     *
     * @param  string  $name
     * @param  string|object|null  $default
     * @return string|object|null
     */
    public function parameter($name, $default = null)
    {
        return Arr::get($this->parameters(), $name, $default);
    }

    /**
     * Get original value of a given parameter from the route.
	 * 从路由中获取给定参数的原始值
     *
     * @param  string  $name
     * @param  string|null  $default
     * @return string|null
     */
    public function originalParameter($name, $default = null)
    {
        return Arr::get($this->originalParameters(), $name, $default);
    }

    /**
     * Set a parameter to the given value.
	 * 将参数设置为给定值
     *
     * @param  string  $name
     * @param  string|object|null  $value
     * @return void
     */
    public function setParameter($name, $value)
    {
        $this->parameters();

        $this->parameters[$name] = $value;
    }

    /**
     * Unset a parameter on the route if it is set.
	 * 如果路由上设置了某个参数，则取消该参数。
     *
     * @param  string  $name
     * @return void
     */
    public function forgetParameter($name)
    {
        $this->parameters();

        unset($this->parameters[$name]);
    }

    /**
     * Get the key / value list of parameters for the route.
	 * 获取路由参数的键/值列表
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function parameters()
    {
        if (isset($this->parameters)) {
            return $this->parameters;
        }

        throw new LogicException('Route is not bound.');
    }

    /**
     * Get the key / value list of original parameters for the route.
	 * 获取路由原始参数的键/值列表
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function originalParameters()
    {
        if (isset($this->originalParameters)) {
            return $this->originalParameters;
        }

        throw new LogicException('Route is not bound.');
    }

    /**
     * Get the key / value list of parameters without null values.
	 * 获取不带空值的参数的键/值列表
     *
     * @return array
     */
    public function parametersWithoutNulls()
    {
        return array_filter($this->parameters(), fn ($p) => ! is_null($p));
    }

    /**
     * Get all of the parameter names for the route.
	 * 获取路由的所有参数名
     *
     * @return array
     */
    public function parameterNames()
    {
        if (isset($this->parameterNames)) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParameterNames();
    }

    /**
     * Get the parameter names for the route.
	 * 获取路由的参数名
     *
     * @return array
     */
    protected function compileParameterNames()
    {
        preg_match_all('/\{(.*?)\}/', $this->getDomain().$this->uri, $matches);

        return array_map(fn ($m) => trim($m, '?'), $matches[1]);
    }

    /**
     * Get the parameters that are listed in the route / controller signature.
	 * 获取路由/控制器签名中列出的参数
     *
     * @param  array  $conditions
     * @return array
     */
    public function signatureParameters($conditions = [])
    {
        if (is_string($conditions)) {
            $conditions = ['subClass' => $conditions];
        }

        return RouteSignatureParameters::fromAction($this->action, $conditions);
    }

    /**
     * Get the binding field for the given parameter.
	 * 获取给定参数的绑定字段
     *
     * @param  string|int  $parameter
     * @return string|null
     */
    public function bindingFieldFor($parameter)
    {
        $fields = is_int($parameter) ? array_values($this->bindingFields) : $this->bindingFields;

        return $fields[$parameter] ?? null;
    }

    /**
     * Get the binding fields for the route.
	 * 获取路由的绑定字段
     *
     * @return array
     */
    public function bindingFields()
    {
        return $this->bindingFields ?? [];
    }

    /**
     * Set the binding fields for the route.
	 * 设置路由的绑定字段
     *
     * @param  array  $bindingFields
     * @return $this
     */
    public function setBindingFields(array $bindingFields)
    {
        $this->bindingFields = $bindingFields;

        return $this;
    }

    /**
     * Get the parent parameter of the given parameter.
	 * 获取给定参数的父参数
     *
     * @param  string  $parameter
     * @return string
     */
    public function parentOfParameter($parameter)
    {
        $key = array_search($parameter, array_keys($this->parameters));

        if ($key === 0) {
            return;
        }

        return array_values($this->parameters)[$key - 1];
    }

    /**
     * Allow "trashed" models to be retrieved when resolving implicit model bindings for this route.
	 * 在解析此路由的隐式模型绑定时，允许检索"垃圾"模型。
     *
     * @param  bool  $withTrashed
     * @return $this
     */
    public function withTrashed($withTrashed = true)
    {
        $this->withTrashedBindings = $withTrashed;

        return $this;
    }

    /**
     * Determines if the route allows "trashed" models to be retrieved when resolving implicit model bindings.
	 * 确定路由是否允许在解析隐式模型绑定时检索“废弃”模型
     *
     * @return bool
     */
    public function allowsTrashedBindings()
    {
        return $this->withTrashedBindings;
    }

    /**
     * Set a default value for the route.
	 * 设置路由的缺省值
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function defaults($key, $value)
    {
        $this->defaults[$key] = $value;

        return $this;
    }

    /**
     * Set the default values for the route.
	 * 配置路由的缺省值
     *
     * @param  array  $defaults
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Set a regular expression requirement on the route.
	 * 在路由上配置正则表达式要求
     *
     * @param  array|string  $name
     * @param  string|null  $expression
     * @return $this
     */
    public function where($name, $expression = null)
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    /**
     * Parse arguments to the where method into an array.
	 * 将where方法的参数解析到数组中
     *
     * @param  array|string  $name
     * @param  string  $expression
     * @return array
     */
    protected function parseWhere($name, $expression)
    {
        return is_array($name) ? $name : [$name => $expression];
    }

    /**
     * Set a list of regular expression requirements on the route.
	 * 设置路由上的正则表达式需求列表
     *
     * @param  array  $wheres
     * @return $this
     */
    public function setWheres(array $wheres)
    {
        foreach ($wheres as $name => $expression) {
            $this->where($name, $expression);
        }

        return $this;
    }

    /**
     * Mark this route as a fallback route.
	 * 把这条路线标记为退路
     *
     * @return $this
     */
    public function fallback()
    {
        $this->isFallback = true;

        return $this;
    }

    /**
     * Set the fallback value.
	 * 设置回退值
     *
     * @param  bool  $isFallback
     * @return $this
     */
    public function setFallback($isFallback)
    {
        $this->isFallback = $isFallback;

        return $this;
    }

    /**
     * Get the HTTP verbs the route responds to.
	 * 获取路由响应的HTTP动词
     *
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Determine if the route only responds to HTTP requests.
	 * 确定路由是否只响应HTTP请求
     *
     * @return bool
     */
    public function httpOnly()
    {
        return in_array('http', $this->action, true);
    }

    /**
     * Determine if the route only responds to HTTPS requests.
	 * 确定路由是否只响应HTTPS请求
     *
     * @return bool
     */
    public function httpsOnly()
    {
        return $this->secure();
    }

    /**
     * Determine if the route only responds to HTTPS requests.
	 * 确定路由是否只响应HTTPS请求
     *
     * @return bool
     */
    public function secure()
    {
        return in_array('https', $this->action, true);
    }

    /**
     * Get or set the domain for the route.
	 * 获取或设置路由的域
     *
     * @param  string|null  $domain
     * @return $this|string|null
     */
    public function domain($domain = null)
    {
        if (is_null($domain)) {
            return $this->getDomain();
        }

        $parsed = RouteUri::parse($domain);

        $this->action['domain'] = $parsed->uri;

        $this->bindingFields = array_merge(
            $this->bindingFields, $parsed->bindingFields
        );

        return $this;
    }

    /**
     * Get the domain defined for the route.
	 * 获取为路由定义的域
     *
     * @return string|null
     */
    public function getDomain()
    {
        return isset($this->action['domain'])
                ? str_replace(['http://', 'https://'], '', $this->action['domain']) : null;
    }

    /**
     * Get the prefix of the route instance.
	 * 获取路由实例的前缀
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->action['prefix'] ?? null;
    }

    /**
     * Add a prefix to the route URI.
	 * 为路由URI添加前
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $prefix ??= '';

        $this->updatePrefixOnAction($prefix);

        $uri = rtrim($prefix, '/').'/'.ltrim($this->uri, '/');

        return $this->setUri($uri !== '/' ? trim($uri, '/') : $uri);
    }

    /**
     * Update the "prefix" attribute on the action array.
	 * 更新动作数组中的"prefix"属性
     *
     * @param  string  $prefix
     * @return void
     */
    protected function updatePrefixOnAction($prefix)
    {
        if (! empty($newPrefix = trim(rtrim($prefix, '/').'/'.ltrim($this->action['prefix'] ?? '', '/'), '/'))) {
            $this->action['prefix'] = $newPrefix;
        }
    }

    /**
     * Get the URI associated with the route.
	 * 获取与路由关联的URI
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Set the URI that the route responds to.
	 * 设置路由响应的URI
     *
     * @param  string  $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $this->parseUri($uri);

        return $this;
    }

    /**
     * Parse the route URI and normalize / store any implicit binding fields.
	 * 解析路由URI并规范化/存储任何隐式绑定字段
     *
     * @param  string  $uri
     * @return string
     */
    protected function parseUri($uri)
    {
        $this->bindingFields = [];

        return tap(RouteUri::parse($uri), function ($uri) {
            $this->bindingFields = $uri->bindingFields;
        })->uri;
    }

    /**
     * Get the name of the route instance.
	 * 获取路由实例的名称
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->action['as'] ?? null;
    }

    /**
     * Add or change the route name.
	 * 添加或修改路由名称
     *
     * @param  string  $name
     * @return $this
     */
    public function name($name)
    {
        $this->action['as'] = isset($this->action['as']) ? $this->action['as'].$name : $name;

        return $this;
    }

    /**
     * Determine whether the route's name matches the given patterns.
	 * 确定路由的名称是否与给定的模式匹配
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function named(...$patterns)
    {
        if (is_null($routeName = $this->getName())) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the handler for the route.
	 * 设置路由的处理程序
     *
     * @param  \Closure|array|string  $action
     * @return $this
     */
    public function uses($action)
    {
        if (is_array($action)) {
            $action = $action[0].'@'.$action[1];
        }

        $action = is_string($action) ? $this->addGroupNamespaceToStringUses($action) : $action;

        return $this->setAction(array_merge($this->action, $this->parseAction([
            'uses' => $action,
            'controller' => $action,
        ])));
    }

    /**
     * Parse a string based action for the "uses" fluent method.
	 * 为"uses"流畅方法解析一个基于字符串的动作
     *
     * @param  string  $action
     * @return string
     */
    protected function addGroupNamespaceToStringUses($action)
    {
        $groupStack = last($this->router->getGroupStack());

        if (isset($groupStack['namespace']) && ! str_starts_with($action, '\\')) {
            return $groupStack['namespace'].'\\'.$action;
        }

        return $action;
    }

    /**
     * Get the action name for the route.
	 * 获取路由的动作名称
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->action['controller'] ?? 'Closure';
    }

    /**
     * Get the method name of the route action.
	 * 获取路由操作的方法名
     *
     * @return string
     */
    public function getActionMethod()
    {
        return Arr::last(explode('@', $this->getActionName()));
    }

    /**
     * Get the action array or one of its properties for the route.
	 * 获取该路由的动作数组或其中一个属性
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function getAction($key = null)
    {
        return Arr::get($this->action, $key);
    }

    /**
     * Set the action array for the route.
	 * 设置路由的动作数组
     *
     * @param  array  $action
     * @return $this
     */
    public function setAction(array $action)
    {
        $this->action = $action;

        if (isset($this->action['domain'])) {
            $this->domain($this->action['domain']);
        }

        return $this;
    }

    /**
     * Get the value of the action that should be taken on a missing model exception.
	 * 获取应该对缺失模型异常采取的操作的值
     *
     * @return \Closure|null
     */
    public function getMissing()
    {
        $missing = $this->action['missing'] ?? null;

        return is_string($missing) &&
            Str::startsWith($missing, [
                'O:47:"Laravel\\SerializableClosure\\SerializableClosure',
            ]) ? unserialize($missing) : $missing;
    }

    /**
     * Define the callable that should be invoked on a missing model exception.
	 * 定义在缺失模型异常时应该调用的可调用对象
     *
     * @param  \Closure  $missing
     * @return $this
     */
    public function missing($missing)
    {
        $this->action['missing'] = $missing;

        return $this;
    }

    /**
     * Get all middleware, including the ones from the controller.
	 * 获取所有中间件，包括来自控制器的中间件。
     *
     * @return array
     */
    public function gatherMiddleware()
    {
        if (! is_null($this->computedMiddleware)) {
            return $this->computedMiddleware;
        }

        $this->computedMiddleware = [];

        return $this->computedMiddleware = Router::uniqueMiddleware(array_merge(
            $this->middleware(), $this->controllerMiddleware()
        ));
    }

    /**
     * Get or set the middlewares attached to the route.
	 * 获取或设置附加到路由的中间件
     *
     * @param  array|string|null  $middleware
     * @return $this|array
     */
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return (array) ($this->action['middleware'] ?? []);
        }

        if (! is_array($middleware)) {
            $middleware = func_get_args();
        }

        foreach ($middleware as $index => $value) {
            $middleware[$index] = (string) $value;
        }

        $this->action['middleware'] = array_merge(
            (array) ($this->action['middleware'] ?? []), $middleware
        );

        return $this;
    }

    /**
     * Specify that the "Authorize" / "can" middleware should be applied to the route with the given options.
	 * 指定应该将"Authorize"/"can"中间件应用于具有给定选项的路由。
     *
     * @param  string  $ability
     * @param  array|string  $models
     * @return $this
     */
    public function can($ability, $models = [])
    {
        return empty($models)
                    ? $this->middleware(['can:'.$ability])
                    : $this->middleware(['can:'.$ability.','.implode(',', Arr::wrap($models))]);
    }

    /**
     * Get the middleware for the route's controller.
	 * 获取路由控制器的中间件
     *
     * @return array
     */
    public function controllerMiddleware()
    {
        if (! $this->isControllerAction()) {
            return [];
        }

        [$controllerClass, $controllerMethod] = [
            $this->getControllerClass(),
            $this->getControllerMethod(),
        ];

        if (is_a($controllerClass, HasMiddleware::class, true)) {
            return $this->staticallyProvidedControllerMiddleware(
                $controllerClass, $controllerMethod
            );
        }

        if (method_exists($controllerClass, 'getMiddleware')) {
            return $this->controllerDispatcher()->getMiddleware(
                $this->getController(), $controllerMethod
            );
        }

        return [];
    }

    /**
     * Get the statically provided controller middleware for the given class and method.
	 * 获取为给定类和方法静态提供的控制器中间件
     *
     * @param  string  $class
     * @param  string  $method
     * @return array
     */
    protected function staticallyProvidedControllerMiddleware(string $class, string $method)
    {
        return collect($class::middleware())->reject(function ($middleware) use ($method) {
            return $this->controllerDispatcher()::methodExcludedByOptions(
                $method, ['only' => $middleware->only, 'except' => $middleware->except]
            );
        })->map->middleware->values()->all();
    }

    /**
     * Specify middleware that should be removed from the given route.
	 * 指定应该从给定路由中删除的中间件
     *
     * @param  array|string  $middleware
     * @return $this
     */
    public function withoutMiddleware($middleware)
    {
        $this->action['excluded_middleware'] = array_merge(
            (array) ($this->action['excluded_middleware'] ?? []), Arr::wrap($middleware)
        );

        return $this;
    }

    /**
     * Get the middleware should be removed from the route.
	 * Get中间件应该从路由中移除
     *
     * @return array
     */
    public function excludedMiddleware()
    {
        return (array) ($this->action['excluded_middleware'] ?? []);
    }

    /**
     * Indicate that the route should enforce scoping of multiple implicit Eloquent bindings.
	 * 指示路由应该强制多个隐式Eloquent绑定的作用域
     *
     * @return $this
     */
    public function scopeBindings()
    {
        $this->action['scope_bindings'] = true;

        return $this;
    }

    /**
     * Indicate that the route should not enforce scoping of multiple implicit Eloquent bindings.
	 * 指示路由不应该强制多个隐式Eloquent绑定的作用域
     *
     * @return $this
     */
    public function withoutScopedBindings()
    {
        $this->action['scope_bindings'] = false;

        return $this;
    }

    /**
     * Determine if the route should enforce scoping of multiple implicit Eloquent bindings.
	 * 确定路由是否应该强制多个隐式Eloquent绑定的作用域
     *
     * @return bool
     */
    public function enforcesScopedBindings()
    {
        return (bool) ($this->action['scope_bindings'] ?? false);
    }

    /**
     * Determine if the route should prevent scoping of multiple implicit Eloquent bindings.
	 * 确定路由是否应该防止多个隐式Eloquent绑定的作用域
     *
     * @return bool
     */
    public function preventsScopedBindings()
    {
        return isset($this->action['scope_bindings']) && $this->action['scope_bindings'] === false;
    }

    /**
     * Specify that the route should not allow concurrent requests from the same session.
	 * 指定路由不允许来自同一会话的并发请求
     *
     * @param  int|null  $lockSeconds
     * @param  int|null  $waitSeconds
     * @return $this
     */
    public function block($lockSeconds = 10, $waitSeconds = 10)
    {
        $this->lockSeconds = $lockSeconds;
        $this->waitSeconds = $waitSeconds;

        return $this;
    }

    /**
     * Specify that the route should allow concurrent requests from the same session.
	 * 指定路由应该允许来自同一会话的并发请求
     *
     * @return $this
     */
    public function withoutBlocking()
    {
        return $this->block(null, null);
    }

    /**
     * Get the maximum number of seconds the route's session lock should be held for.
	 * 获取路由会话锁应该保持的最大秒数
     *
     * @return int|null
     */
    public function locksFor()
    {
        return $this->lockSeconds;
    }

    /**
     * Get the maximum number of seconds to wait while attempting to acquire a session lock.
	 * 获取尝试获取会话锁时等待的最大秒数
     *
     * @return int|null
     */
    public function waitsFor()
    {
        return $this->waitSeconds;
    }

    /**
     * Get the dispatcher for the route's controller.
	 * 获取路由控制器的调度程序
     *
     * @return \Illuminate\Routing\Contracts\ControllerDispatcher
     */
    public function controllerDispatcher()
    {
        if ($this->container->bound(ControllerDispatcherContract::class)) {
            return $this->container->make(ControllerDispatcherContract::class);
        }

        return new ControllerDispatcher($this->container);
    }

    /**
     * Get the route validators for the instance.
	 * 获取实例的路由验证器
     *
     * @return array
     */
    public static function getValidators()
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }

        // To match the route, we will use a chain of responsibility pattern with the
        // validator implementations. We will spin through each one making sure it
        // passes and then we will know if the route as a whole matches request.
		// 为了匹配路由，我们将使用责任链模式。
        return static::$validators = [
            new UriValidator, new MethodValidator,
            new SchemeValidator, new HostValidator,
        ];
    }

    /**
     * Convert the route to a Symfony route.
	 * 将路由转换为Symfony路由
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function toSymfonyRoute()
    {
        return new SymfonyRoute(
            preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->uri()), $this->getOptionalParameterNames(),
            $this->wheres, ['utf8' => true],
            $this->getDomain() ?: '', [], $this->methods
        );
    }

    /**
     * Get the optional parameter names for the route.
	 * 获取路由的可选参数名
     *
     * @return array
     */
    protected function getOptionalParameterNames()
    {
        preg_match_all('/\{(\w+?)\?\}/', $this->uri(), $matches);

        return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
    }

    /**
     * Get the compiled version of the route.
	 * 获取路由的编译版本
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function getCompiled()
    {
        return $this->compiled;
    }

    /**
     * Set the router instance on the route.
	 * 设置路由上的router实例
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

    /**
     * Prepare the route instance for serialization.
	 * 为序列化准备路由实例
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function prepareForSerialization()
    {
        if ($this->action['uses'] instanceof Closure) {
            $this->action['uses'] = serialize(
                new SerializableClosure($this->action['uses'])
            );
        }

        if (isset($this->action['missing']) && $this->action['missing'] instanceof Closure) {
            $this->action['missing'] = serialize(
                new SerializableClosure($this->action['missing'])
            );
        }

        $this->compileRoute();

        unset($this->router, $this->container);
    }

    /**
     * Dynamically access route parameters.
	 * 动态访问路由参数
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->parameter($key);
    }
}
