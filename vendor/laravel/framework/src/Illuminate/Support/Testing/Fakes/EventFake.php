<?php
/**
 * Illuminate，支持，测试，假装，假事件
 */

namespace Illuminate\Support\Testing\Fakes;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ReflectsClosures;
use PHPUnit\Framework\Assert as PHPUnit;
use ReflectionFunction;

class EventFake implements Dispatcher
{
    use ReflectsClosures;

    /**
     * The original event dispatcher.
	 * 原始事件调度程序
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * The event types that should be intercepted instead of dispatched.
	 * 应该拦截而不是分派的事件类型
     *
     * @var array
     */
    protected $eventsToFake;

    /**
     * All of the events that have been intercepted keyed by type.
	 * 所有被截获的事件都是按类型键入的
     *
     * @var array
     */
    protected $events = [];

    /**
     * Create a new event fake instance.
	 * 创建一个新的事件伪实例
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
     * @param  array|string  $eventsToFake
     * @return void
     */
    public function __construct(Dispatcher $dispatcher, $eventsToFake = [])
    {
        $this->dispatcher = $dispatcher;

        $this->eventsToFake = Arr::wrap($eventsToFake);
    }

    /**
     * Assert if an event has a listener attached to it.
	 * 如果事件附加了侦听器，则断言。
     *
     * @param  string  $expectedEvent
     * @param  string  $expectedListener
     * @return void
     */
    public function assertListening($expectedEvent, $expectedListener)
    {
        foreach ($this->dispatcher->getListeners($expectedEvent) as $listenerClosure) {
            $actualListener = (new ReflectionFunction($listenerClosure))
                        ->getStaticVariables()['listener'];

            if (is_string($actualListener) && Str::endsWith($actualListener, '@handle')) {
                $actualListener = Str::parseCallback($actualListener)[0];
            }

            if ($actualListener === $expectedListener ||
                ($actualListener instanceof Closure &&
                $expectedListener === Closure::class)) {
                PHPUnit::assertTrue(true);

                return;
            }
        }

        PHPUnit::assertTrue(
            false,
            sprintf(
                'Event [%s] does not have the [%s] listener attached to it',
                $expectedEvent,
                print_r($expectedListener, true)
            )
        );
    }

    /**
     * Assert if an event was dispatched based on a truth-test callback.
	 * 判断事件是否基于真值测试回调分派
     *
     * @param  string|\Closure  $event
     * @param  callable|int|null  $callback
     * @return void
     */
    public function assertDispatched($event, $callback = null)
    {
        if ($event instanceof Closure) {
            [$event, $callback] = [$this->firstClosureParameterType($event), $event];
        }

        if (is_int($callback)) {
            return $this->assertDispatchedTimes($event, $callback);
        }

        PHPUnit::assertTrue(
            $this->dispatched($event, $callback)->count() > 0,
            "The expected [{$event}] event was not dispatched."
        );
    }

    /**
     * Assert if an event was dispatched a number of times.
	 * 断言事件是否被多次调度
     *
     * @param  string  $event
     * @param  int  $times
     * @return void
     */
    public function assertDispatchedTimes($event, $times = 1)
    {
        $count = $this->dispatched($event)->count();

        PHPUnit::assertSame(
            $times, $count,
            "The expected [{$event}] event was dispatched {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if an event was dispatched based on a truth-test callback.
	 * 确定是否根据真值测试回调分派事件
     *
     * @param  string|\Closure  $event
     * @param  callable|null  $callback
     * @return void
     */
    public function assertNotDispatched($event, $callback = null)
    {
        if ($event instanceof Closure) {
            [$event, $callback] = [$this->firstClosureParameterType($event), $event];
        }

        PHPUnit::assertCount(
            0, $this->dispatched($event, $callback),
            "The unexpected [{$event}] event was dispatched."
        );
    }

    /**
     * Assert that no events were dispatched.
	 * 断言没有分派任何事件
     *
     * @return void
     */
    public function assertNothingDispatched()
    {
        $count = count(Arr::flatten($this->events));

        PHPUnit::assertSame(
            0, $count,
            "{$count} unexpected events were dispatched."
        );
    }

    /**
     * Get all of the events matching a truth-test callback.
	 * 获取与true-test回调匹配的所有事件
     *
     * @param  string  $event
     * @param  callable|null  $callback
     * @return \Illuminate\Support\Collection
     */
    public function dispatched($event, $callback = null)
    {
        if (! $this->hasDispatched($event)) {
            return collect();
        }

        $callback = $callback ?: function () {
            return true;
        };

        return collect($this->events[$event])->filter(function ($arguments) use ($callback) {
            return $callback(...$arguments);
        });
    }

    /**
     * Determine if the given event has been dispatched.
	 * 确定是否已分派给定的事件
     *
     * @param  string  $event
     * @return bool
     */
    public function hasDispatched($event)
    {
        return isset($this->events[$event]) && ! empty($this->events[$event]);
    }

    /**
     * Register an event listener with the dispatcher.
	 * 向调度程序注册事件监听器
     *
     * @param  \Closure|string|array  $events
     * @param  mixed  $listener
     * @return void
     */
    public function listen($events, $listener = null)
    {
        $this->dispatcher->listen($events, $listener);
    }

    /**
     * Determine if a given event has listeners.
	 * 确定给定事件是否有监听器
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasListeners($eventName)
    {
        return $this->dispatcher->hasListeners($eventName);
    }

    /**
     * Register an event and payload to be dispatched later.
	 * 注册以后要分派的事件和有效负载
     *
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function push($event, $payload = [])
    {
        //
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
        $this->dispatcher->subscribe($subscriber);
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
        //
    }

    /**
     * Fire an event and call the listeners.
	 * 触发一个事件并调用监听器
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        $name = is_object($event) ? get_class($event) : (string) $event;

        if ($this->shouldFakeEvent($name, $payload)) {
            $this->events[$name][] = func_get_args();
        } else {
            return $this->dispatcher->dispatch($event, $payload, $halt);
        }
    }

    /**
     * Determine if an event should be faked or actually dispatched.
	 * 确定是否应该伪造或实际分派事件
     *
     * @param  string  $eventName
     * @param  mixed  $payload
     * @return bool
     */
    protected function shouldFakeEvent($eventName, $payload)
    {
        if (empty($this->eventsToFake)) {
            return true;
        }

        return collect($this->eventsToFake)
            ->filter(function ($event) use ($eventName, $payload) {
                return $event instanceof Closure
                            ? $event($eventName, $payload)
                            : $event === $eventName;
            })
            ->isNotEmpty();
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
        //
    }

    /**
     * Forget all of the queued listeners.
	 * 忘记所有排队的监听器
     *
     * @return void
     */
    public function forgetPushed()
    {
        //
    }

    /**
     * Dispatch an event and call the listeners.
	 * 分派事件并调用监听器
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return array|null
     */
    public function until($event, $payload = [])
    {
        return $this->dispatch($event, $payload, true);
    }
}
