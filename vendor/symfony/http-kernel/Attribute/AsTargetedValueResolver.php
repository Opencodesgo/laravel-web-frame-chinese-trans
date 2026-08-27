<?php
/**
 * Symfony，Component，HttpKernel，属性，AsTargetedValueResolver
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Attribute;

/**
 * Service tag to autoconfigure targeted value resolvers.
 * 服务标记以自动配置目标值解析器。
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsTargetedValueResolver
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
