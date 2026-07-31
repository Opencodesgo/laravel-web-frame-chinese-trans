<?php
/**
 * Illuminate，路由，路由器核心类
 */

namespace Illuminate\Routing;

use ArrayObject;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use ReflectionClass;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @mixin \Illuminate\Routing\RouteRegistrar
 */
class Router implements BindingRegistrar, RegistrarContract
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The event dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The IoC container instance.
	 * IoC容器实例
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The route collection instance.
	 * 路由收集实例
     *
     * @var \Illuminate\Routing\RouteCollectionInterface
     */
    protected $routes;

    /**
     * The currently dispatched route instance.
	 * 当前调度的路由实例
     *
     * @var \Illuminate\Routing\Route|null
     */
    protected $current;

    /**
     * The request currently being dispatched.
	 * 当前正在调度的请求
     *
     * @var \Illuminate\Http\Request
     */
    protected $currentRequest;

    /**
     * All of the short-hand keys for middlewares.
	 * 所有中间件的快捷键
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the middleware groups.
	 * 所有中间件组
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The priority-sorted list of middleware.
	 * 中间件的优先级排序列表
     *
     * Forces the listed middleware to always be in the given order.
	 * 强制列出的中间件始终按照给定的顺序排列
     *
     * @var array
     */
    public $middlewarePriority = [];

    /**
     * The registered route value binders.
	 * 注册路由值绑定
     *
     * @var array
     */
    protected $binders = [];

    /**
     * The globally available parameter patterns.
	 * 全局可用的参数模式
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * The route group attribute stack.
	 * 路由组属性栈
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * All of the verbs supported by the router.
	 * 路由器支持的所有动词
     *
     * @var string[]
     */
    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Create a new Router instance.
	 * 创建一个新的Router实例
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  \Illuminate\Container\Container|null  $container
     * @return void
     */
    public function __construct(Dispatcher $events, Container $container = null)
    {
        $this->events = $events;
        $this->routes = new RouteCollection;
        $this->container = $container ?: new Container;
    }

    /**
     * Register a new GET route with the router.
	 * 向路由器注册一个新的GET路由
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function get($uri, $action = null)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a new POST route with the router.
	 * 向路由器注册一个新的POST路由
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function post($uri, $action = null)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a new PUT route with the router.
	 * 向路由器注册一条新的PUT路由
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function put($uri, $action = null)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
	 * 向路由器注册一条新的PATCH路由
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function patch($uri, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
	 * 向路由器注册一条新的DELETE路由
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function delete($uri, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a new OPTIONS route with the router.
	 * 向路由器注册一个新的OPTIONS路由
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function options($uri, $action = null)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Register a new route responding to all verbs.
	 * 注册一个响应所有动词的新路由
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function any($uri, $action = null)
    {
        return $this->addRoute(self::$verbs, $uri, $action);
    }

    /**
     * Register a new Fallback route with the router.
	 * 向路由器注册一个新的回退路由
     *
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function fallback($action)
    {
        $placeholder = 'fallbackPlaceholder';

        return $this->addRoute(
            'GET', "{{$placeholder}}", $action
        )->where($placeholder, '.*')->fallback();
    }

    /**
     * Create a redirect from one URI to another.
	 * 创建从一个URI到另一个URI的重定向
     *
     * @param  string  $uri
     * @param  string  $destination
     * @param  int  $status
     * @return \Illuminate\Routing\Route
     */
    public function redirect($uri, $destination, $status = 302)
    {
        return $this->any($uri, '\Illuminate\Routing\RedirectController')
                ->defaults('destination', $destination)
                ->defaults('status', $status);
    }

    /**
     * Create a permanent redirect from one URI to another.
	 * 创建从一个URI到另一个URI的永久重定向
     *
     * @param  string  $uri
     * @param  string  $destination
     * @return \Illuminate\Routing\Route
     */
    public function permanentRedirect($uri, $destination)
    {
        return $this->redirect($uri, $destination, 301);
    }

    /**
     * Register a new route that returns a view.
	 * 注册一个返回视图的新路由
     *
     * @param  string  $uri
     * @param  string  $view
     * @param  array  $data
     * @param  int|array  $status
     * @param  array  $headers
     * @return \Illuminate\Routing\Route
     */
    public function view($uri, $view, $data = [], $status = 200, array $headers = [])
    {
        return $this->match(['GET', 'HEAD'], $uri, '\Illuminate\Routing\ViewController')
                ->setDefaults([
                    'view' => $view,
                    'data' => $data,
                    'status' => is_array($status) ? 200 : $status,
                    'headers' => is_array($status) ? $status : $headers,
                ]);
    }

    /**
     * Register a new route with the given verbs.
	 * 用给定的动词注册一条新路线
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function match($methods, $uri, $action = null)
    {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }

    /**
     * Register an array of resource controllers.
	 * 注册一个资源控制器数组
     *
     * @param  array  $resources
     * @param  array  $options
     * @return void
     */
    public function resources(array $resources, array $options = [])
    {
        foreach ($resources as $name => $controller) {
            $this->resource($name, $controller, $options);
        }
    }

    /**
     * Route a resource to a controller.
	 * 将资源路由到控制器
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function resource($name, $controller, array $options = [])
    {
        if ($this->container && $this->container->bound(ResourceRegistrar::class)) {
            $registrar = $this->container->make(ResourceRegistrar::class);
        } else {
            $registrar = new ResourceRegistrar($this);
        }

        return new PendingResourceRegistration(
            $registrar, $name, $controller, $options
        );
    }

    /**
     * Register an array of API resource controllers.
	 * 注册一个API资源控制器数组
     *
     * @param  array  $resources
     * @param  array  $options
     * @return void
     */
    public function apiResources(array $resources, array $options = [])
    {
        foreach ($resources as $name => $controller) {
            $this->apiResource($name, $controller, $options);
        }
    }

    /**
     * Route an API resource to a controller.
	 * 将API资源路由到控制器
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function apiResource($name, $controller, array $options = [])
    {
        $only = ['index', 'show', 'store', 'update', 'destroy'];

        if (isset($options['except'])) {
            $only = array_diff($only, (array) $options['except']);
        }

        return $this->resource($name, $controller, array_merge([
            'only' => $only,
        ], $options));
    }

    /**
     * Create a route group with shared attributes.
	 * 创建具有共享属性的路由组
     *
     * @param  array  $attributes
     * @param  \Closure|string  $routes
     * @return void
     */
    public function group(array $attributes, $routes)
    {
        $this->updateGroupStack($attributes);

        // Once we have updated the group stack, we'll load the provided routes and
        // merge in the group's attributes when the routes are created. After we
        // have created the routes, we will pop the attributes off the stack.
		// 一旦我们更新了组堆栈，我们将加载提供的路由并合并组的属性。
        $this->loadRoutes($routes);

        array_pop($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
	 * 用给定的属性更新组堆栈
     *
     * @param  array  $attributes
     * @return void
     */
    protected function updateGroupStack(array $attributes)
    {
        if ($this->hasGroupStack()) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
	 * 将给定的数组与最后一个组堆栈合并
     *
     * @param  array  $new
     * @param  bool  $prependExistingPrefix
     * @return array
     */
    public function mergeWithLastGroup($new, $prependExistingPrefix = true)
    {
        return RouteGroup::merge($new, end($this->groupStack), $prependExistingPrefix);
    }

    /**
     * Load the provided routes.
	 * 加载提供的路由
     *
     * @param  \Closure|string  $routes
     * @return void
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            (new RouteFileRegistrar($this))->register($routes);
        }
    }

    /**
     * Get the prefix from the last group on the stack.
	 * 从堆栈上的最后一个组获取前缀
     *
     * @return string
     */
    public function getLastGroupPrefix()
    {
        if ($this->hasGroupStack()) {
            $last = end($this->groupStack);

            return $last['prefix'] ?? '';
        }

        return '';
    }

    /**
     * Add a route to the underlying route collection.
	 * 向底层路由集合添加路由
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function addRoute($methods, $uri, $action)
    {
        return $this->routes->add($this->createRoute($methods, $uri, $action));
    }

    /**
     * Create a new route instance.
	 * 创建一个新的路由实例
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \Illuminate\Routing\Route
     */
    protected function createRoute($methods, $uri, $action)
    {
        // If the route is routing to a controller we will parse the route action into
        // an acceptable array format before registering it and creating this route
        // instance itself. We need to build the Closure that will call this out.
		// 如果路由要路由到一个控制器，我们将把路由动作解析为在注册之前使用可接受的数组格式。
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }

        $route = $this->newRoute(
            $methods, $this->prefix($uri), $action
        );

        // If we have groups that need to be merged, we will merge them now after this
        // route has already been created and is ready to go. After we're done with
        // the merge we will be ready to return the route back out to the caller.
		// 如果我们有需要合并的组，我们将在此之后合并它们。
        if ($this->hasGroupStack()) {
            $this->mergeGroupAttributesIntoRoute($route);
        }

        $this->addWhereClausesToRoute($route);

        return $route;
    }

    /**
     * Determine if the action is routing to a controller.
	 * 确定动作是否路由到控制器。
     *
     * @param  mixed  $action
     * @return bool
     */
    protected function actionReferencesController($action)
    {
        if (! $action instanceof Closure) {
            return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
        }

        return false;
    }

    /**
     * Add a controller based route action to the action array.
	 * 向动作数组中添加一个基于控制器的路由动作
     *
     * @param  array|string  $action
     * @return array
     */
    protected function convertToControllerAction($action)
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        // Here we'll merge any group "controller" and "uses" statements if necessary so that
        // the action has the proper clause for this property. Then, we can simply set the
        // name of this controller on the action plus return the action array for usage.
		// 在这里，我们将合并任何组"controller"和"uses"语句。
        if ($this->hasGroupStack()) {
            $action['uses'] = $this->prependGroupController($action['uses']);
            $action['uses'] = $this->prependGroupNamespace($action['uses']);
        }

        // Here we will set this controller name on the action array just so we always
        // have a copy of it for reference if we need it. This can be used while we
        // search for a controller name or do some other type of fetch operation.
		// 这里我们将在动作数组上设置控制器名。
        $action['controller'] = $action['uses'];

        return $action;
    }

    /**
     * Prepend the last group namespace onto the use clause.
	 * 将最后一个组名称空间前置到use子句
     *
     * @param  string  $class
     * @return string
     */
    protected function prependGroupNamespace($class)
    {
        $group = end($this->groupStack);

        return isset($group['namespace']) && strpos($class, '\\') !== 0
                ? $group['namespace'].'\\'.$class : $class;
    }

    /**
     * Prepend the last group controller onto the use clause.
	 * 将最后一个组控制器添加到use子句中
     *
     * @param  string  $class
     * @return string
     */
    protected function prependGroupController($class)
    {
        $group = end($this->groupStack);

        if (! isset($group['controller'])) {
            return $class;
        }

        if (class_exists($class)) {
            return $class;
        }

        if (strpos($class, '@') !== false) {
            return $class;
        }

        return $group['controller'].'@'.$class;
    }

    /**
     * Create a new Route object.
	 * 创建一个新的Route对象
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \Illuminate\Routing\Route
     */
    public function newRoute($methods, $uri, $action)
    {
        return (new Route($methods, $uri, $action))
                    ->setRouter($this)
                    ->setContainer($this->container);
    }

    /**
     * Prefix the given URI with the last prefix.
	 * 用最后一个前缀作为给定URI的前缀
     *
     * @param  string  $uri
     * @return string
     */
    protected function prefix($uri)
    {
        return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
    }

    /**
     * Add the necessary where clauses to the route based on its initial registration.
	 * 根据路由的初始注册添加必要的where子句
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return \Illuminate\Routing\Route
     */
    protected function addWhereClausesToRoute($route)
    {
        $route->where(array_merge(
            $this->patterns, $route->getAction()['where'] ?? []
        ));

        return $route;
    }

    /**
     * Merge the group stack with the controller action.
	 * 将组堆叠与控制器动作合并
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     */
    protected function mergeGroupAttributesIntoRoute($route)
    {
        $route->setAction($this->mergeWithLastGroup(
            $route->getAction(),
            $prependExistingPrefix = false
        ));
    }

    /**
     * Return the response returned by the given route.
	 * 返回由给定路由返回的响应
     *
     * @param  string  $name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondWithRoute($name)
    {
        $route = tap($this->routes->getByName($name))->bind($this->currentRequest);

        return $this->runRoute($this->currentRequest, $route);
    }

    /**
     * Dispatch the request to the application.
	 * 将请求分派给应用程序
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;

        return $this->dispatchToRoute($request);
    }

    /**
     * Dispatch the request to a route and return the response.
	 * 将请求分派到路由并返回响应
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dispatchToRoute(Request $request)
    {
        return $this->runRoute($request, $this->findRoute($request));
    }

    /**
     * Find the route matching a given request.
	 * 找到与给定请求匹配的路由
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Routing\Route
     */
    protected function findRoute($request)
    {
        $this->current = $route = $this->routes->match($request);

        $route->setContainer($this->container);

        $this->container->instance(Route::class, $route);

        return $route;
    }

    /**
     * Return the response for the given route.
	 * 返回给定路由的响应
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Routing\Route  $route
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function runRoute(Request $request, Route $route)
    {
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $this->events->dispatch(new RouteMatched($route, $request));

        return $this->prepareResponse($request,
            $this->runRouteWithinStack($route, $request)
        );
    }

    /**
     * Run the given route within a Stack "onion" instance.
	 * 在Stack"onion"实例中运行给定的路由
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function runRouteWithinStack(Route $route, Request $request)
    {
        $shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
                                $this->container->make('middleware.disable') === true;

        $middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);

        return (new Pipeline($this->container))
                        ->send($request)
                        ->through($middleware)
                        ->then(function ($request) use ($route) {
                            return $this->prepareResponse(
                                $request, $route->run()
                            );
                        });
    }

    /**
     * Gather the middleware for the given route with resolved class names.
	 * 收集具有解析类名的给定路由的中间件
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    public function gatherRouteMiddleware(Route $route)
    {
        $computedMiddleware = $route->gatherMiddleware();

        $excluded = collect($route->excludedMiddleware())->map(function ($name) {
            return (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups);
        })->flatten()->values()->all();

        $middleware = collect($computedMiddleware)->map(function ($name) {
            return (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups);
        })->flatten()->reject(function ($name) use ($excluded) {
            if (empty($excluded)) {
                return false;
            }

            if ($name instanceof Closure) {
                return false;
            }

            if (in_array($name, $excluded, true)) {
                return true;
            }

            if (! class_exists($name)) {
                return false;
            }

            $reflection = new ReflectionClass($name);

            return collect($excluded)->contains(function ($exclude) use ($reflection) {
                return class_exists($exclude) && $reflection->isSubclassOf($exclude);
            });
        })->values();

        return $this->sortMiddleware($middleware);
    }

    /**
     * Sort the given middleware by priority.
	 * 按优先级对给定的中间件排序
     *
     * @param  \Illuminate\Support\Collection  $middlewares
     * @return array
     */
    protected function sortMiddleware(Collection $middlewares)
    {
        return (new SortedMiddleware($this->middlewarePriority, $middlewares))->all();
    }

    /**
     * Create a response instance from the given value.
	 * 根据给定的值创建响应实例
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  mixed  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepareResponse($request, $response)
    {
        return static::toResponse($request, $response);
    }

    /**
     * Static version of prepareResponse.
	 * prepareerresponse的静态版本
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  mixed  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function toResponse($request, $response)
    {
        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        }

        if ($response instanceof PsrResponseInterface) {
            $response = (new HttpFoundationFactory)->createResponse($response);
        } elseif ($response instanceof Model && $response->wasRecentlyCreated) {
            $response = new JsonResponse($response, 201);
        } elseif ($response instanceof Stringable) {
            $response = new Response($response->__toString(), 200, ['Content-Type' => 'text/html']);
        } elseif (! $response instanceof SymfonyResponse &&
                   ($response instanceof Arrayable ||
                    $response instanceof Jsonable ||
                    $response instanceof ArrayObject ||
                    $response instanceof JsonSerializable ||
                    $response instanceof \stdClass ||
                    is_array($response))) {
            $response = new JsonResponse($response);
        } elseif (! $response instanceof SymfonyResponse) {
            $response = new Response($response, 200, ['Content-Type' => 'text/html']);
        }

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response->prepare($request);
    }

    /**
     * Substitute the route bindings onto the route.
	 * 将路由绑定替换到路由上
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return \Illuminate\Routing\Route
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function substituteBindings($route)
    {
        foreach ($route->parameters() as $key => $value) {
            if (isset($this->binders[$key])) {
                $route->setParameter($key, $this->performBinding($key, $value, $route));
            }
        }

        return $route;
    }

    /**
     * Substitute the implicit Eloquent model bindings for the route.
	 * 将隐式Eloquent模型绑定替换为路由
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function substituteImplicitBindings($route)
    {
        ImplicitRouteBinding::resolveForRoute($this->container, $route);
    }

    /**
     * Call the binding callback for the given key.
	 * 调用给定键的绑定回调
     *
     * @param  string  $key
     * @param  string  $value
     * @param  \Illuminate\Routing\Route  $route
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function performBinding($key, $value, $route)
    {
        return call_user_func($this->binders[$key], $value, $route);
    }

    /**
     * Register a route matched event listener.
	 * 注册一个路由匹配的事件监听器
     *
     * @param  string|callable  $callback
     * @return void
     */
    public function matched($callback)
    {
        $this->events->listen(Events\RouteMatched::class, $callback);
    }

    /**
     * Get all of the defined middleware short-hand names.
	 * 得到所有已定义的中间件简写名称
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Register a short-hand name for a middleware.
	 * 为中间件注册一个简写名称
     *
     * @param  string  $name
     * @param  string  $class
     * @return $this
     */
    public function aliasMiddleware($name, $class)
    {
        $this->middleware[$name] = $class;

        return $this;
    }

    /**
     * Check if a middlewareGroup with the given name exists.
	 * 检查是否存在具有给定名称的middlewareGroup
     *
     * @param  string  $name
     * @return bool
     */
    public function hasMiddlewareGroup($name)
    {
        return array_key_exists($name, $this->middlewareGroups);
    }

    /**
     * Get all of the defined middleware groups.
	 * 得到所有已定义的中间件组
     *
     * @return array
     */
    public function getMiddlewareGroups()
    {
        return $this->middlewareGroups;
    }

    /**
     * Register a group of middleware.
	 * 注册一组中间件
     *
     * @param  string  $name
     * @param  array  $middleware
     * @return $this
     */
    public function middlewareGroup($name, array $middleware)
    {
        $this->middlewareGroups[$name] = $middleware;

        return $this;
    }

    /**
     * Add a middleware to the beginning of a middleware group.
	 * 在中间件组的开头添加一个中间件
     *
     * If the middleware is already in the group, it will not be added again.
	 * 如果中间件已经在组中，则不会再次添加它。
     *
     * @param  string  $group
     * @param  string  $middleware
     * @return $this
     */
    public function prependMiddlewareToGroup($group, $middleware)
    {
        if (isset($this->middlewareGroups[$group]) && ! in_array($middleware, $this->middlewareGroups[$group])) {
            array_unshift($this->middlewareGroups[$group], $middleware);
        }

        return $this;
    }

    /**
     * Add a middleware to the end of a middleware group.
	 * 在中间件组的末尾添加一个中间件
     *
     * If the middleware is already in the group, it will not be added again.
	 * 如果中间件已经在组中，则不会再次添加它。
     *
     * @param  string  $group
     * @param  string  $middleware
     * @return $this
     */
    public function pushMiddlewareToGroup($group, $middleware)
    {
        if (! array_key_exists($group, $this->middlewareGroups)) {
            $this->middlewareGroups[$group] = [];
        }

        if (! in_array($middleware, $this->middlewareGroups[$group])) {
            $this->middlewareGroups[$group][] = $middleware;
        }

        return $this;
    }

    /**
     * Flush the router's middleware groups.
	 * 刷新路由器的中间件组
     *
     * @return $this
     */
    public function flushMiddlewareGroups()
    {
        $this->middlewareGroups = [];

        return $this;
    }

    /**
     * Add a new route parameter binder.
	 * 添加一个新的路由参数绑定器
     *
     * @param  string  $key
     * @param  string|callable  $binder
     * @return void
     */
    public function bind($key, $binder)
    {
        $this->binders[str_replace('-', '_', $key)] = RouteBinding::forCallback(
            $this->container, $binder
        );
    }

    /**
     * Register a model binder for a wildcard.
	 * 为通配符注册一个模型绑定器
     *
     * @param  string  $key
     * @param  string  $class
     * @param  \Closure|null  $callback
     * @return void
     */
    public function model($key, $class, Closure $callback = null)
    {
        $this->bind($key, RouteBinding::forModel($this->container, $class, $callback));
    }

    /**
     * Get the binding callback for a given binding.
	 * 获取给定绑定的绑定回调
     *
     * @param  string  $key
     * @return \Closure|null
     */
    public function getBindingCallback($key)
    {
        if (isset($this->binders[$key = str_replace('-', '_', $key)])) {
            return $this->binders[$key];
        }
    }

    /**
     * Get the global "where" patterns.
	 * 获取全局"where"模式
     *
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * Set a global where pattern on all routes.
	 * 在所有路由上设置全局where模式
     *
     * @param  string  $key
     * @param  string  $pattern
     * @return void
     */
    public function pattern($key, $pattern)
    {
        $this->patterns[$key] = $pattern;
    }

    /**
     * Set a group of global where patterns on all routes.
	 * 在所有路由上设置一组全局where模式
     *
     * @param  array  $patterns
     * @return void
     */
    public function patterns($patterns)
    {
        foreach ($patterns as $key => $pattern) {
            $this->pattern($key, $pattern);
        }
    }

    /**
     * Determine if the router currently has a group stack.
	 * 确定路由器当前是否有组堆栈
     *
     * @return bool
     */
    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }

    /**
     * Get the current group stack for the router.
	 * 获取路由器的当前组堆栈
     *
     * @return array
     */
    public function getGroupStack()
    {
        return $this->groupStack;
    }

    /**
     * Get a route parameter for the current route.
	 * 获取当前路由的路由参数
     *
     * @param  string  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        return $this->current()->parameter($key, $default);
    }

    /**
     * Get the request currently being dispatched.
	 * 获取当前正在分派的请求
     *
     * @return \Illuminate\Http\Request
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * Get the currently dispatched route instance.
	 * 获取当前调度的路由实例
     *
     * @return \Illuminate\Routing\Route|null
     */
    public function getCurrentRoute()
    {
        return $this->current();
    }

    /**
     * Get the currently dispatched route instance.
	 * 获取当前调度的路由实例
     *
     * @return \Illuminate\Routing\Route|null
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Check if a route with the given name exists.
	 * 检查给定名称的路由是否存在
     *
     * @param  string  $name
     * @return bool
     */
    public function has($name)
    {
        $names = is_array($name) ? $name : func_get_args();

        foreach ($names as $value) {
            if (! $this->routes->hasNamedRoute($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the current route name.
	 * 得到当前路由名称
     *
     * @return string|null
     */
    public function currentRouteName()
    {
        return $this->current() ? $this->current()->getName() : null;
    }

    /**
     * Alias for the "currentRouteNamed" method.
	 * "currentRouteNamed"方法的别名
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function is(...$patterns)
    {
        return $this->currentRouteNamed(...$patterns);
    }

    /**
     * Determine if the current route matches a pattern.
	 * 确定当前路由是否与模式匹配
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function currentRouteNamed(...$patterns)
    {
        return $this->current() && $this->current()->named(...$patterns);
    }

    /**
     * Get the current route action.
	 * 获取当前的路由动作
     *
     * @return string|null
     */
    public function currentRouteAction()
    {
        if ($this->current()) {
            return $this->current()->getAction()['controller'] ?? null;
        }
    }

    /**
     * Alias for the "currentRouteUses" method.
	 * "currentRouteUses"方法的别名
     *
     * @param  array  ...$patterns
     * @return bool
     */
    public function uses(...$patterns)
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $this->currentRouteAction())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current route action matches a given action.
	 * 确定当前路由操作是否与给定操作匹配
     *
     * @param  string  $action
     * @return bool
     */
    public function currentRouteUses($action)
    {
        return $this->currentRouteAction() == $action;
    }

    /**
     * Set the unmapped global resource parameters to singular.
	 * 将未映射的全局资源参数设置为singular
     *
     * @param  bool  $singular
     * @return void
     */
    public function singularResourceParameters($singular = true)
    {
        ResourceRegistrar::singularParameters($singular);
    }

    /**
     * Set the global resource parameter mapping.
	 * 设置全局资源参数映射
     *
     * @param  array  $parameters
     * @return void
     */
    public function resourceParameters(array $parameters = [])
    {
        ResourceRegistrar::setParameters($parameters);
    }

    /**
     * Get or set the verbs used in the resource URIs.
	 * 获取或设置资源uri中使用的谓词
     *
     * @param  array  $verbs
     * @return array|null
     */
    public function resourceVerbs(array $verbs = [])
    {
        return ResourceRegistrar::verbs($verbs);
    }

    /**
     * Get the underlying route collection.
	 * 获取底层路由集合
     *
     * @return \Illuminate\Routing\RouteCollectionInterface
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set the route collection instance.
	 * 设置路由采集实例
     *
     * @param  \Illuminate\Routing\RouteCollection  $routes
     * @return void
     */
    public function setRoutes(RouteCollection $routes)
    {
        foreach ($routes as $route) {
            $route->setRouter($this)->setContainer($this->container);
        }

        $this->routes = $routes;

        $this->container->instance('routes', $this->routes);
    }

    /**
     * Set the compiled route collection instance.
	 * 设置编译路由集合实例
     *
     * @param  array  $routes
     * @return void
     */
    public function setCompiledRoutes(array $routes)
    {
        $this->routes = (new CompiledRouteCollection($routes['compiled'], $routes['attributes']))
            ->setRouter($this)
            ->setContainer($this->container);

        $this->container->instance('routes', $this->routes);
    }

    /**
     * Remove any duplicate middleware from the given array.
	 * 从给定数组中删除任何重复的中间件
     *
     * @param  array  $middleware
     * @return array
     */
    public static function uniqueMiddleware(array $middleware)
    {
        $seen = [];
        $result = [];

        foreach ($middleware as $value) {
            $key = \is_object($value) ? \spl_object_id($value) : $value;

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Set the container instance used by the router.
	 * 设置路由器使用的容器实例
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
     * Dynamically handle calls into the router instance.
	 * 动态处理对路由器实例的调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if ($method === 'middleware') {
            return (new RouteRegistrar($this))->attribute($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
        }

        return (new RouteRegistrar($this))->attribute($method, array_key_exists(0, $parameters) ? $parameters[0] : true);
    }
}
