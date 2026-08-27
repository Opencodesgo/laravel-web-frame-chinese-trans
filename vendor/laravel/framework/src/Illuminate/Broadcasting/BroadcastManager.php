<?php
/**
 * Illuminate，广播，广播管理者
 */

namespace Illuminate\Broadcasting;

use Ably\AblyRest;
use Closure;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Broadcasting\Broadcasters\AblyBroadcaster;
use Illuminate\Broadcasting\Broadcasters\LogBroadcaster;
use Illuminate\Broadcasting\Broadcasters\NullBroadcaster;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Broadcasting\Broadcasters\RedisBroadcaster;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Broadcasting\Factory as FactoryContract;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcherContract;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Foundation\CachesRoutes;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Pusher\Pusher;

/**
 * @mixin \Illuminate\Contracts\Broadcasting\Broadcaster
 */
class BroadcastManager implements FactoryContract
{
    /**
     * The application instance.
	 * 应用实例
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * The array of resolved broadcast drivers.
	 * 已解析的广播驱动程序数组
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * The registered custom driver creators.
	 * 注册的自定义驱动程序创建者
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new manager instance.
	 * 创建新的管理器实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register the routes for handling broadcast channel authentication and sockets.
	 * 注册处理广播通道认证和套接字的路由
     *
     * @param  array|null  $attributes
     * @return void
     */
    public function routes(array $attributes = null)
    {
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        $attributes = $attributes ?: ['middleware' => ['web']];

        $this->app['router']->group($attributes, function ($router) {
            $router->match(
                ['get', 'post'], '/broadcasting/auth',
                '\\'.BroadcastController::class.'@authenticate'
            )->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        });
    }

    /**
     * Register the routes for handling broadcast user authentication.
	 * 注册处理广播用户认证的路由
     *
     * @param  array|null  $attributes
     * @return void
     */
    public function userRoutes(array $attributes = null)
    {
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        $attributes = $attributes ?: ['middleware' => ['web']];

        $this->app['router']->group($attributes, function ($router) {
            $router->match(
                ['get', 'post'], '/broadcasting/user-auth',
                '\\'.BroadcastController::class.'@authenticateUser'
            )->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        });
    }

    /**
     * Register the routes for handling broadcast authentication and sockets.
	 * 注册处理广播认证和套接字的路由
     *
     * Alias of "routes" method.
     *
     * @param  array|null  $attributes
     * @return void
     */
    public function channelRoutes(array $attributes = null)
    {
        return $this->routes($attributes);
    }

    /**
     * Get the socket ID for the given request.
	 * 获取给定请求的套接字ID
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return string|null
     */
    public function socket($request = null)
    {
        if (! $request && ! $this->app->bound('request')) {
            return;
        }

        $request = $request ?: $this->app['request'];

        return $request->header('X-Socket-ID');
    }

    /**
     * Begin broadcasting an event.
	 * 开始广播事件
     *
     * @param  mixed|null  $event
     * @return \Illuminate\Broadcasting\PendingBroadcast
     */
    public function event($event = null)
    {
        return new PendingBroadcast($this->app->make('events'), $event);
    }

    /**
     * Queue the given event for broadcast.
	 * 将给定的事件排队以进行广播
     *
     * @param  mixed  $event
     * @return void
     */
    public function queue($event)
    {
        if ($event instanceof ShouldBroadcastNow ||
            (is_object($event) &&
             method_exists($event, 'shouldBroadcastNow') &&
             $event->shouldBroadcastNow())) {
            return $this->app->make(BusDispatcherContract::class)->dispatchNow(new BroadcastEvent(clone $event));
        }

        $queue = null;

        if (method_exists($event, 'broadcastQueue')) {
            $queue = $event->broadcastQueue();
        } elseif (isset($event->broadcastQueue)) {
            $queue = $event->broadcastQueue;
        } elseif (isset($event->queue)) {
            $queue = $event->queue;
        }

        $broadcastEvent = new BroadcastEvent(clone $event);

        if ($event instanceof ShouldBeUnique) {
            $broadcastEvent = new UniqueBroadcastEvent(clone $event);

            if ($this->mustBeUniqueAndCannotAcquireLock($broadcastEvent)) {
                return;
            }
        }

        $this->app->make('queue')
            ->connection($event->connection ?? null)
            ->pushOn($queue, $broadcastEvent);
    }

    /**
     * Determine if the broadcastable event must be unique and determine if we can acquire the necessary lock.
	 * 确定可广播事件是否必须是唯一的，并确定我们是否可以获得必要的锁。
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function mustBeUniqueAndCannotAcquireLock($event)
    {
        return ! (new UniqueLock(
            method_exists($event, 'uniqueVia')
                ? $event->uniqueVia()
                : $this->app->make(Cache::class)
        ))->acquire($event);
    }

    /**
     * Get a driver instance.
	 * 得到驱动实例
     *
     * @param  string|null  $driver
     * @return mixed
     */
    public function connection($driver = null)
    {
        return $this->driver($driver);
    }

    /**
     * Get a driver instance.
	 * 得到驱动实例
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function driver($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] = $this->get($name);
    }

    /**
     * Attempt to get the connection from the local cache.
	 * 尝试从本地缓存获取连接
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    protected function get($name)
    {
        return $this->drivers[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given broadcaster.
	 * 解析给定广播程序
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Broadcast connection [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (! method_exists($this, $driverMethod)) {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }

        return $this->{$driverMethod}($config);
    }

    /**
     * Call a custom driver creator.
	 * 调用自定义驱动程序创建者
     *
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Create an instance of the driver.
	 * 创建驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    protected function createPusherDriver(array $config)
    {
        return new PusherBroadcaster($this->pusher($config));
    }

    /**
     * Get a Pusher instance for the given configuration.
	 * 获取给定配置的push实例
     *
     * @param  array  $config
     * @return \Pusher\Pusher
     */
    public function pusher(array $config)
    {
        $pusher = new Pusher(
            $config['key'],
            $config['secret'],
            $config['app_id'],
            $config['options'] ?? [],
            isset($config['client_options']) && ! empty($config['client_options'])
                    ? new GuzzleClient($config['client_options'])
                    : null,
        );

        if ($config['log'] ?? false) {
            $pusher->setLogger($this->app->make(LoggerInterface::class));
        }

        return $pusher;
    }

    /**
     * Create an instance of the driver.
	 * 创建驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    protected function createAblyDriver(array $config)
    {
        return new AblyBroadcaster($this->ably($config));
    }

    /**
     * Get an Ably instance for the given configuration.
	 * 获取给定配置的able实例
     *
     * @param  array  $config
     * @return \Ably\AblyRest
     */
    public function ably(array $config)
    {
        return new AblyRest($config);
    }

    /**
     * Create an instance of the driver.
	 * 创建驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    protected function createRedisDriver(array $config)
    {
        return new RedisBroadcaster(
            $this->app->make('redis'), $config['connection'] ?? null,
            $this->app['config']->get('database.redis.options.prefix', '')
        );
    }

    /**
     * Create an instance of the driver.
	 * 创建驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    protected function createLogDriver(array $config)
    {
        return new LogBroadcaster(
            $this->app->make(LoggerInterface::class)
        );
    }

    /**
     * Create an instance of the driver.
	 * 创建驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    protected function createNullDriver(array $config)
    {
        return new NullBroadcaster;
    }

    /**
     * Get the connection configuration.
	 * 获取连接配置
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        if (! is_null($name) && $name !== 'null') {
            return $this->app['config']["broadcasting.connections.{$name}"];
        }

        return ['driver' => 'null'];
    }

    /**
     * Get the default driver name.
	 * 获取默认驱动程序名称
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['broadcasting.default'];
    }

    /**
     * Set the default driver name.
	 * 设置默认驱动程序名称
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['broadcasting.default'] = $name;
    }

    /**
     * Disconnect the given disk and remove from local cache.
	 * 断开给定磁盘的连接并从本地缓存中删除
     *
     * @param  string|null  $name
     * @return void
     */
    public function purge($name = null)
    {
        $name ??= $this->getDefaultDriver();

        unset($this->drivers[$name]);
    }

    /**
     * Register a custom driver creator Closure.
	 * 注册自定义驱动程序创建器Closure
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get the application instance used by the manager.
	 * 获取管理器使用的应用程序实例
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Set the application instance used by the manager.
	 * 设置管理员使用的应用实例
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return $this
     */
    public function setApplication($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Forget all of the resolved driver instances.
	 * 忘记所有已解析的驱动程序实例
     *
     * @return $this
     */
    public function forgetDrivers()
    {
        $this->drivers = [];

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
	 * 动态调用默认驱动程序实例
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
