<?php
/**
 * Symfony，Component，Translation，依赖注入，翻译提取器通行证
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged translation.extractor services to translation extractor.
 * 添加标记翻译。提取器服务翻译提取器。
 */
class TranslationExtractorPass implements CompilerPassInterface
{
    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('translation.extractor')) {
            return;
        }

        $definition = $container->getDefinition('translation.extractor');

        foreach ($container->findTaggedServiceIds('translation.extractor', true) as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new RuntimeException(\sprintf('The alias for the tag "translation.extractor" of service "%s" must be set.', $id));
            }

            $definition->addMethodCall('addExtractor', [$attributes[0]['alias'], new Reference($id)]);
        }
    }
}
