<?php
/**
 * Symfony，Component，事件调度器，依赖注入，添加事件别名通过
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\EventDispatcher\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This pass allows bundles to extend the list of event aliases.
 * 此通道允许bundle扩展事件别名列表。
 *
 * @author Alexander M. Turek <me@derrabus.de>
 */
class AddEventAliasesPass implements CompilerPassInterface
{
    private array $eventAliases;

    public function __construct(array $eventAliases)
    {
        $this->eventAliases = $eventAliases;
    }

    public function process(ContainerBuilder $container): void
    {
        $eventAliases = $container->hasParameter('event_dispatcher.event_aliases') ? $container->getParameter('event_dispatcher.event_aliases') : [];

        $container->setParameter(
            'event_dispatcher.event_aliases',
            array_merge($eventAliases, $this->eventAliases)
        );
    }
}
