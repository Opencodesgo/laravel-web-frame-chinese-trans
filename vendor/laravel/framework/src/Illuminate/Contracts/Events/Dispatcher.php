<?php
/**
 * Illuminate，契约，事件，调度员
 */

namespace Illuminate\Contracts\Events;

interface Dispatcher
{
    /**
     * Register an event listener with the dispatcher.
	 * 向调度程序注册事件侦听器
     *
     * @param  string|array  $events
     * @param  \Closure|string  $listener
     * @return void
     */
    public function listen($events, $listener);

    /**
     * Determine if a given event has listeners.
	 * 确定给定事件是否有侦听器
     *
     * @param  string  $eventName
     * @return bool
     */
    public function hasListeners($eventName);

    /**
     * Register an event subscriber with the dispatcher.
	 * 向调度程序注册事件订阅者
     *
     * @param  object|string  $subscriber
     * @return void
     */
    public function subscribe($subscriber);

    /**
     * Dispatch an event until the first non-null response is returned.
	 * 调度一个事件，直到返回第一个非空响应。
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @return array|null
     */
    public function until($event, $payload = []);

    /**
     * Dispatch an event and call the listeners.
	 * 分派事件并调用侦听器
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false);

    /**
     * Register an event and payload to be fired later.
	 * 注册稍后要触发的事件和有效负载
     *
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function push($event, $payload = []);

    /**
     * Flush a set of pushed events.
	 * 刷新一组推送的事件
     *
     * @param  string  $event
     * @return void
     */
    public function flush($event);

    /**
     * Remove a set of listeners from the dispatcher.
	 * 从调度程序中删除一组侦听器
     *
     * @param  string  $event
     * @return void
     */
    public function forget($event);

    /**
     * Forget all of the queued listeners.
	 * 忘记所有排队的侦听器
     *
     * @return void
     */
    public function forgetPushed();
}
