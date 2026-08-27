<?php
/**
 * Symfony，Component，EventDispatcher，事件调度器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Contracts\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

/**
 * Allows providing hooks on domain-specific lifecycles by dispatching events.
 * 允许通过调度事件在特定领域的生命周期上提供钩子。
 */
interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * Dispatches an event to all registered listeners.
	 * 将事件分派给所有已注册的侦听器。
     *
     * @template T of object
     *
     * @param T           $event     The event to pass to the event handlers/listeners
     * @param string|null $eventName The name of the event to dispatch. If not supplied,
     *                               the class of $event should be used instead.
     *
     * @return T The passed $event MUST be returned
     */
    public function dispatch(object $event, ?string $eventName = null): object;
}
