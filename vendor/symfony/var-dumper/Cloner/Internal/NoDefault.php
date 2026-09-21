<?php
/**
 * Symfony，Component，VarDumper，克隆，内部，No Default
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Cloner\Internal;

/**
 * Flags a typed property that has no default value.
 * 标记没有默认值的类型化属性。
 *
 * This dummy object is used to distinguish a property with a default value of null
 * from a property that is uninitialized by default.
 * 此占位符对象用于区分具有默认值为 null 的属性和默认未初始化的属性。
 *
 * @internal
 */
enum NoDefault
{
    case NoDefault;
}
