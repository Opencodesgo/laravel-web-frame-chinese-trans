<?php
/**
 * Symfony，Component，VarDumper，Caster，未初始化的存根
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Caster;

/**
 * Represents an uninitialized property.
 * 表示未初始化的属性。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class UninitializedStub extends ConstStub
{
    public function __construct(\ReflectionProperty $property)
    {
        parent::__construct('?'.($property->hasType() ? ' '.$property->getType() : ''), 'Uninitialized property');
    }
}
