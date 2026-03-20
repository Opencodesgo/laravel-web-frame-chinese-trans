<?php
/**
 * Psr，EventDispatcher，事件调度接口
 */

declare(strict_types=1);

namespace Psr\EventDispatcher;

/**
 * Defines a dispatcher for events.
 * 定义事件调度程序
 */
interface EventDispatcherInterface
{
    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    public function dispatch(object $event);
}
