<?php
/**
 * Psr，EventDispatcher，监听程序提供程序接口
 */

declare(strict_types=1);

namespace Psr\EventDispatcher;

/**
 * Mapper from an event to the listeners that are applicable to that event.
 * 从事件映射到适用于该事件的侦听器
 */
interface ListenerProviderInterface
{
    /**
     * @param object $event
     *   An event for which to return the relevant listeners.
     * @return iterable[callable]
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event) : iterable;
}
