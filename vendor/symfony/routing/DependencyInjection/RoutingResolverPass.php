<?php
/**
 * Symfony，Component，Routing，依赖注入，路由解析器通过
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged routing.loader services to routing.resolver service.
 * 添加标记路由。加载服务到路由。解析器服务
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RoutingResolverPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('routing.resolver')) {
            return;
        }

        $definition = $container->getDefinition('routing.resolver');

        foreach ($this->findAndSortTaggedServices('routing.loader', $container) as $id) {
            $definition->addMethodCall('addLoader', [new Reference($id)]);
        }
    }
}
