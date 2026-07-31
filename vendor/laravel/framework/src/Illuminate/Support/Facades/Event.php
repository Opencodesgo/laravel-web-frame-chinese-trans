<?php
/**
 * Illuminate，支持，门面，事件
 */

namespace Illuminate\Support\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Testing\Fakes\EventFake;

/**
 * @method static \Closure createClassListener(string $listener, bool $wildcard = false)
 * @method static \Closure makeListener(\Closure|string $listener, bool $wildcard = false)
 * @method static \Illuminate\Events\Dispatcher setQueueResolver(callable $resolver)
 * @method static array getListeners(string $eventName)
 * @method static array|null dispatch(string|object $event, mixed $payload = [], bool $halt = false)
 * @method static array|null until(string|object $event, mixed $payload = [])
 * @method static bool hasListeners(string $eventName)
 * @method static void assertDispatched(string|\Closure $event, callable|int $callback = null)
 * @method static void assertDispatchedTimes(string $event, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $event, callable|int $callback = null)
 * @method static void assertNothingDispatched()
 * @method static void assertListening(string $expectedEvent, string $expectedListener)
 * @method static void flush(string $event)
 * @method static void forget(string $event)
 * @method static void forgetPushed()
 * @method static void listen(\Closure|string|array $events, \Closure|string|array $listener = null)
 * @method static void push(string $event, array $payload = [])
 * @method static void subscribe(object|string $subscriber)
 *
 * @see \Illuminate\Events\Dispatcher
 */
class Event extends Facade
{
    /**
     * Replace the bound instance with a fake.
	 * 替换绑定实例为伪实例
     *
     * @param  array|string  $eventsToFake
     * @return \Illuminate\Support\Testing\Fakes\EventFake
     */
    public static function fake($eventsToFake = [])
    {
        static::swap($fake = new EventFake(static::getFacadeRoot(), $eventsToFake));

        Model::setEventDispatcher($fake);
        Cache::refreshEventDispatcher();

        return $fake;
    }

    /**
     * Replace the bound instance with a fake that fakes all events except the given events.
	 * 将绑定实例替换为一个fake，该fake接受除给定事件之外的所有事件。
     *
     * @param  string[]|string  $eventsToAllow
     * @return \Illuminate\Support\Testing\Fakes\EventFake
     */
    public static function fakeExcept($eventsToAllow)
    {
        return static::fake([
            function ($eventName) use ($eventsToAllow) {
                return ! in_array($eventName, (array) $eventsToAllow);
            },
        ]);
    }

    /**
     * Replace the bound instance with a fake during the given callable's execution.
	 * 在给定的可调用对象执行期间，将绑定实例替换为伪实例。
     *
     * @param  callable  $callable
     * @param  array  $eventsToFake
     * @return mixed
     */
    public static function fakeFor(callable $callable, array $eventsToFake = [])
    {
        $originalDispatcher = static::getFacadeRoot();

        static::fake($eventsToFake);

        return tap($callable(), function () use ($originalDispatcher) {
            static::swap($originalDispatcher);

            Model::setEventDispatcher($originalDispatcher);
            Cache::refreshEventDispatcher();
        });
    }

    /**
     * Replace the bound instance with a fake during the given callable's execution.
	 * 在给定的可调用对象执行期间，将绑定实例替换为伪实例。
     *
     * @param  callable  $callable
     * @param  array  $eventsToAllow
     * @return mixed
     */
    public static function fakeExceptFor(callable $callable, array $eventsToAllow = [])
    {
        $originalDispatcher = static::getFacadeRoot();

        static::fakeExcept($eventsToAllow);

        return tap($callable(), function () use ($originalDispatcher) {
            static::swap($originalDispatcher);

            Model::setEventDispatcher($originalDispatcher);
            Cache::refreshEventDispatcher();
        });
    }

    /**
     * Get the registered name of the component.
	 * 获取组件的注册名称
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'events';
    }
}
