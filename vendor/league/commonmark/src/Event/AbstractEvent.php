<?php
/**
 * League，CommonMark，Event，抽象事件
 */

/**
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the Symfony EventDispatcher "Event" contract
 *  - (c) 2018-2019 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Event;

/**
 * Base class for classes containing event data.
 * 包含事件数据的类的基类
 *
 * This class contains no event data. It is used by events that do not pass
 * state information to an event handler when an event is raised.
 *
 * You can call the method stopPropagation() to abort the execution of
 * further listeners in your event listener.
 */
abstract class AbstractEvent
{
    /** @var bool */
    private $propagationStopped = false;

    /**
     * Returns whether further event listeners should be triggered.
	 * 返回是否应该触发进一步的事件侦听器
     */
    final public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
	 * 停止将事件传播到其他事件侦听器
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    final public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
