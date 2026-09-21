<?php
/**
 * Illuminate，事件，调度程序
 */

namespace Illuminate\Events;

use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\ReflectsClosures;
use ReflectionClass;

class Dispatcher implements DispatcherContract
{
    use Macroable, ReflectsClosures;

    /**
     * The IoC container instance.
	 * IoC容器实例
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The registered event listeners.
	 * 已注册的事件侦听器
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * The wildcard listeners.
	 * 通配符侦听器
     *
     * @var array
     */
    protected $wildcards = [];

    /**
     * The cached wildcard listeners.
	 * 缓存的通配符侦听器
     *
     * @var array
     */
    protected $wildcardsCache = [];

    /**
     * The queue resolver instance.
	 * 队列解析器实例
     *
     * @var callable
     */
    protected $queueResolver;

    /**
     * Create a new event dispatcher instance.
	 * 创建一个新的事件调度程序实例
     *
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(ContainerContract $container = null)
    {
        $this->container = $container ?: new Container;
    }

    /**
     * Register an event listener with the dispatcher.
	 * 向调度程序注册事件侦听器
     *
     * @param  \Closure|string|array  $events
     * @param  \Closure|string|array|null  $listener
     * @return void
     */
    public function listen($events, $listener = null)
    {
        if ($events instanceof Closure) {
            return collect($this->firstClosureParameterTypes($events))
                ->each(function ($event) use ($events) {
                    $this->listen($event, $events);
                });
        } elseif ($events instanceof QueuedClosure) {
            return collect($this->firstClosureParameterTypes($events->closure))
                ->each(function ($event) use ($events) {
                    $this->listen($event, $events->resolve());
                });
        } elseif ($listener instanceof QueuedClosure) {
            $listener = $listener->resolve();
        }

        foreach ((array) $events as $event) {
            if (str_contains($event, '*')) {
                $this->setupWildcardListen($event, $listener);
            } else {
                $this->listeners[$event][] = $listener;
            }
        }
    }

    /**
     * Setup a wildcard listener callback.
	 * 设置一个通配符侦听器回调
     *
     * @param  string  $event
     * @param  \Closure|string  $listener
     * @return void
     */
    protected function setupWildcardListen($event, $listener)
    {
        $this->wildcards[$event][] = $listener;

        $this->wildcardsCache = [];
    }

    /**
     * Determine if a given event has listeners.
	 * 确定给定事件是否有侦听器
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) ||
               isset($this->wildcards[$eventName]) ||
               $this->hasWildcardListeners($eventName);
    }

    /**
     * Determine if the given event has any wildcard listeners.
	 * 确定给定事件是否有任何通配符侦听器
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasWildcardListeners($eventName)
    {
        foreach ($this->wildcards as $key => $listeners) {
            if (Str::is($key, $eventName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register an event and payload to be fired later.
	 * 注册稍后要触发的事件和有效负载
     *
     * @param  string  $event
     * @param  object|array  $payload
     * @return void
     */
    public function push($event, $payload = [])
    {
        $this->listen($event.'_pushed', function () use ($event, $payload) {
            $this->dispatch($event, $payload);
        });
    }

    /**
     * Flush a set of pushed events.
	 * 刷新一组推送的事件
     *
     * @param  string  $event
     * @return void
     */
    public function flush($event)
    {
        $this->dispatch($event.'_pushed');
    }

    /**
     * Register an event subscriber with the dispatcher.
	 * 向调度程序注册事件订阅者
     *
     * @param  object|string  $subscriber
     * @return void
     */
    public function subscribe($subscriber)
    {
        $subscriber = $this->resolveSubscriber($subscriber);

        $events = $subscriber->subscribe($this);

        if (is_array($events)) {
            foreach ($events as $event => $listeners) {
                foreach (Arr::wrap($listeners) as $listener) {
                    if (is_string($listener) && method_exists($subscriber, $listener)) {
                        $this->listen($event, [get_class($subscriber), $listener]);

                        continue;
                    }

                    $this->listen($event, $listener);
                }
            }
        }
    }

    /**
     * Resolve the subscriber instance.
	 * 解析订阅者实例
     *
     * @param  object|string  $subscriber
     * @return mixed
     */
    protected function resolveSubscriber($subscriber)
    {
        if (is_string($subscriber)) {
            return $this->container->make($subscriber);
        }

        return $subscriber;
    }

    /**
     * Fire an event until the first non-null response is returned.
	 * 触发一个事件，直到返回第一个非空响应。
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return array|null
     */
    public function until($event, $payload = [])
    {
        return $this->dispatch($event, $payload, true);
    }

    /**
     * Fire an event and call the listeners.
	 * 触发一个事件并调用侦听器
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        // When the given "event" is actually an object we will assume it is an event
        // object and use the class as the event name and this event itself as the
        // payload to the handler, which makes object based events quite simple.
		// 当给定的"事件"实际上是一个对象时，我们假设这是一个事件对象，并使用类作为事件名称。
        [$event, $payload] = $this->parseEventAndPayload(
            $event, $payload
        );

        if ($this->shouldBroadcast($payload)) {
            $this->broadcastEvent($payload[0]);
        }

        $responses = [];

        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($event, $payload);

            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
			// 如果从侦听器返回响应并且启用了事件停止。
            if ($halt && ! is_null($response)) {
                return $response;
            }

            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
			// 如果从侦听器返回一个布尔值false，我们将停止传播。
            if ($response === false) {
                break;
            }

            $responses[] = $response;
        }

        return $halt ? null : $responses;
    }

    /**
     * Parse the given event and payload and prepare them for dispatching.
	 * 解析给定的事件和有效负载，并为分派做好准备。
     *
     * @param  mixed  $event
     * @param  mixed  $payload
     * @return array
     */
    protected function parseEventAndPayload($event, $payload)
    {
        if (is_object($event)) {
            [$payload, $event] = [[$event], get_class($event)];
        }

        return [$event, Arr::wrap($payload)];
    }

    /**
     * Determine if the payload has a broadcastable event.
	 * 确定有效负载是否具有可广播的事件
     *
     * @param  array  $payload
     * @return bool
     */
    protected function shouldBroadcast(array $payload)
    {
        return isset($payload[0]) &&
               $payload[0] instanceof ShouldBroadcast &&
               $this->broadcastWhen($payload[0]);
    }

    /**
     * Check if the event should be broadcasted by the condition.
	 * 检查是否应该通过条件广播事件
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function broadcastWhen($event)
    {
        return method_exists($event, 'broadcastWhen')
                ? $event->broadcastWhen() : true;
    }

    /**
     * Broadcast the given event class.
	 * 广播给定的事件类
     *
     * @param  \Illuminate\Contracts\Broadcasting\ShouldBroadcast  $event
     * @return void
     */
    protected function broadcastEvent($event)
    {
        $this->container->make(BroadcastFactory::class)->queue($event);
    }

    /**
     * Get all of the listeners for a given event name.
	 * 获取给定事件名称的所有侦听器
     *
     * @param  string  $eventName
     * @return array
     */
    public function getListeners($eventName)
    {
        $listeners = array_merge(
            $this->prepareListeners($eventName),
            $this->wildcardsCache[$eventName] ?? $this->getWildcardListeners($eventName)
        );

        return class_exists($eventName, false)
                    ? $this->addInterfaceListeners($eventName, $listeners)
                    : $listeners;
    }

    /**
     * Get the wildcard listeners for the event.
	 * 获取事件的通配符侦听器
     *
     * @param  string  $eventName
     * @return array
     */
    protected function getWildcardListeners($eventName)
    {
        $wildcards = [];

        foreach ($this->wildcards as $key => $listeners) {
            if (Str::is($key, $eventName)) {
                foreach ($listeners as $listener) {
                    $wildcards[] = $this->makeListener($listener, true);
                }
            }
        }

        return $this->wildcardsCache[$eventName] = $wildcards;
    }

    /**
     * Add the listeners for the event's interfaces to the given array.
	 * 将事件接口的侦听器添加到给定数组中
     *
     * @param  string  $eventName
     * @param  array  $listeners
     * @return array
     */
    protected function addInterfaceListeners($eventName, array $listeners = [])
    {
        foreach (class_implements($eventName) as $interface) {
            if (isset($this->listeners[$interface])) {
                foreach ($this->prepareListeners($interface) as $names) {
                    $listeners = array_merge($listeners, (array) $names);
                }
            }
        }

        return $listeners;
    }

    /**
     * Prepare the listeners for a given event.
	 * 为给定事件准备侦听器
     *
     * @param  string  $eventName
     * @return \Closure[]
     */
    protected function prepareListeners(string $eventName)
    {
        $listeners = [];

        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            $listeners[] = $this->makeListener($listener);
        }

        return $listeners;
    }

    /**
     * Register an event listener with the dispatcher.
	 * 向调度程序注册事件侦听器
     *
     * @param  \Closure|string|array  $listener
     * @param  bool  $wildcard
     * @return \Closure
     */
    public function makeListener($listener, $wildcard = false)
    {
        if (is_string($listener)) {
            return $this->createClassListener($listener, $wildcard);
        }

        if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
            return $this->createClassListener($listener, $wildcard);
        }

        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return $listener($event, $payload);
            }

            return $listener(...array_values($payload));
        };
    }

    /**
     * Create a class based listener using the IoC container.
	 * 使用IoC容器创建基于类的侦听器
     *
     * @param  string  $listener
     * @param  bool  $wildcard
     * @return \Closure
     */
    public function createClassListener($listener, $wildcard = false)
    {
        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return call_user_func($this->createClassCallable($listener), $event, $payload);
            }

            $callable = $this->createClassCallable($listener);

            return $callable(...array_values($payload));
        };
    }

    /**
     * Create the class based event callable.
	 * 创建基于类的事件可调用对象
     *
     * @param  array|string  $listener
     * @return callable
     */
    protected function createClassCallable($listener)
    {
        [$class, $method] = is_array($listener)
                            ? $listener
                            : $this->parseClassCallable($listener);

        if (! method_exists($class, $method)) {
            $method = '__invoke';
        }

        if ($this->handlerShouldBeQueued($class)) {
            return $this->createQueuedHandlerCallable($class, $method);
        }

        $listener = $this->container->make($class);

        return $this->handlerShouldBeDispatchedAfterDatabaseTransactions($listener)
                    ? $this->createCallbackForListenerRunningAfterCommits($listener, $method)
                    : [$listener, $method];
    }

    /**
     * Parse the class listener into class and method.
	 * 将类侦听器解析为类和方法
     *
     * @param  string  $listener
     * @return array
     */
    protected function parseClassCallable($listener)
    {
        return Str::parseCallback($listener, 'handle');
    }

    /**
     * Determine if the event handler class should be queued.
	 * 确定是否应该对事件处理程序类进行排队
     *
     * @param  string  $class
     * @return bool
     */
    protected function handlerShouldBeQueued($class)
    {
        try {
            return (new ReflectionClass($class))->implementsInterface(
                ShouldQueue::class
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create a callable for putting an event handler on the queue.
	 * 创建一个可调用对象，用于将事件处理程序放到队列中。
     *
     * @param  string  $class
     * @param  string  $method
     * @return \Closure
     */
    protected function createQueuedHandlerCallable($class, $method)
    {
        return function () use ($class, $method) {
            $arguments = array_map(function ($a) {
                return is_object($a) ? clone $a : $a;
            }, func_get_args());

            if ($this->handlerWantsToBeQueued($class, $arguments)) {
                $this->queueHandler($class, $method, $arguments);
            }
        };
    }

    /**
     * Determine if the given event handler should be dispatched after all database transactions have committed.
	 * 确定是否应该在所有数据库事务提交后分派给定的事件处理程序
     *
     * @param  object|mixed  $listener
     * @return bool
     */
    protected function handlerShouldBeDispatchedAfterDatabaseTransactions($listener)
    {
        return ($listener->afterCommit ?? null) && $this->container->bound('db.transactions');
    }

    /**
     * Create a callable for dispatching a listener after database transactions.
	 * 创建一个可调用对象，用于在数据库事务之后调度侦听器。
     *
     * @param  mixed  $listener
     * @param  string  $method
     * @return \Closure
     */
    protected function createCallbackForListenerRunningAfterCommits($listener, $method)
    {
        return function () use ($method, $listener) {
            $payload = func_get_args();

            $this->container->make('db.transactions')->addCallback(
                function () use ($listener, $method, $payload) {
                    $listener->$method(...$payload);
                }
            );
        };
    }

    /**
     * Determine if the event handler wants to be queued.
	 * 确定事件处理程序是否要排队
     *
     * @param  string  $class
     * @param  array  $arguments
     * @return bool
     */
    protected function handlerWantsToBeQueued($class, $arguments)
    {
        $instance = $this->container->make($class);

        if (method_exists($instance, 'shouldQueue')) {
            return $instance->shouldQueue($arguments[0]);
        }

        return true;
    }

    /**
     * Queue the handler class.
	 * 将处理程序类排队
     *
     * @param  string  $class
     * @param  string  $method
     * @param  array  $arguments
     * @return void
     */
    protected function queueHandler($class, $method, $arguments)
    {
        [$listener, $job] = $this->createListenerAndJob($class, $method, $arguments);

        $connection = $this->resolveQueue()->connection(method_exists($listener, 'viaConnection')
                    ? (isset($arguments[0]) ? $listener->viaConnection($arguments[0]) : $listener->viaConnection())
                    : $listener->connection ?? null);

        $queue = method_exists($listener, 'viaQueue')
                    ? (isset($arguments[0]) ? $listener->viaQueue($arguments[0]) : $listener->viaQueue())
                    : $listener->queue ?? null;

        isset($listener->delay)
                    ? $connection->laterOn($queue, $listener->delay, $job)
                    : $connection->pushOn($queue, $job);
    }

    /**
     * Create the listener and job for a queued listener.
	 * 为队列侦听器创建侦听器和作业
     *
     * @param  string  $class
     * @param  string  $method
     * @param  array  $arguments
     * @return array
     */
    protected function createListenerAndJob($class, $method, $arguments)
    {
        $listener = (new ReflectionClass($class))->newInstanceWithoutConstructor();

        return [$listener, $this->propagateListenerOptions(
            $listener, new CallQueuedListener($class, $method, $arguments)
        )];
    }

    /**
     * Propagate listener options to the job.
	 * 将侦听器选项传播到作业
     *
     * @param  mixed  $listener
     * @param  \Illuminate\Events\CallQueuedListener  $job
     * @return mixed
     */
    protected function propagateListenerOptions($listener, $job)
    {
        return tap($job, function ($job) use ($listener) {
            $data = array_values($job->data);

            $job->afterCommit = property_exists($listener, 'afterCommit') ? $listener->afterCommit : null;
            $job->backoff = method_exists($listener, 'backoff') ? $listener->backoff(...$data) : ($listener->backoff ?? null);
            $job->maxExceptions = $listener->maxExceptions ?? null;
            $job->retryUntil = method_exists($listener, 'retryUntil') ? $listener->retryUntil(...$data) : null;
            $job->shouldBeEncrypted = $listener instanceof ShouldBeEncrypted;
            $job->timeout = $listener->timeout ?? null;
            $job->tries = $listener->tries ?? null;

            $job->through(array_merge(
                method_exists($listener, 'middleware') ? $listener->middleware(...$data) : [],
                $listener->middleware ?? []
            ));
        });
    }

    /**
     * Remove a set of listeners from the dispatcher.
	 * 从调度程序中删除一组侦听器
     *
     * @param  string  $event
     * @return void
     */
    public function forget($event)
    {
        if (str_contains($event, '*')) {
            unset($this->wildcards[$event]);
        } else {
            unset($this->listeners[$event]);
        }

        foreach ($this->wildcardsCache as $key => $listeners) {
            if (Str::is($event, $key)) {
                unset($this->wildcardsCache[$key]);
            }
        }
    }

    /**
     * Forget all of the pushed listeners.
	 * 忘记所有被强迫的听众
     *
     * @return void
     */
    public function forgetPushed()
    {
        foreach ($this->listeners as $key => $value) {
            if (str_ends_with($key, '_pushed')) {
                $this->forget($key);
            }
        }
    }

    /**
     * Get the queue implementation from the resolver.
	 * 从解析器获取队列实
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    protected function resolveQueue()
    {
        return call_user_func($this->queueResolver);
    }

    /**
     * Set the queue resolver implementation.
	 * 设置队列解析器实现
     *
     * @param  callable  $resolver
     * @return $this
     */
    public function setQueueResolver(callable $resolver)
    {
        $this->queueResolver = $resolver;

        return $this;
    }

    /**
     * Gets the raw, unprepared listeners.
	 * 得到原始的、毫无准备的听众。
     *
     * @return array
     */
    public function getRawListeners()
    {
        return $this->listeners;
    }
}
