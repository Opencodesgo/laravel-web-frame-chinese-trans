<?php
/**
 * Symfony，Component，HttpKernel，属性，参数接口
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

trigger_deprecation('symfony/http-kernel', '5.3', 'The "%s" interface is deprecated.', ArgumentInterface::class);

/**
 * Marker interface for controller argument attributes.
 * 控制器参数属性的标记接口
 *
 * @deprecated since Symfony 5.3
 */
interface ArgumentInterface
{
}
