<?php
/**
 * Symfony，Component，HttpKernel，属性，作为控制器
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
 * Service tag to autoconfigure controllers.
 * Service标签来自动配置控制器
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AsController
{
    public function __construct()
    {
    }
}
