<?php
/**
 * Symfony，组件，VarDumper，Caster，枚举存根
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

use Symfony\Component\VarDumper\Cloner\Stub;

/**
 * Represents an enumeration of values.
 * 表示值的枚举。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class EnumStub extends Stub
{
    public $dumpKeys = true;

    public function __construct(array $values, bool $dumpKeys = true)
    {
        $this->value = $values;
        $this->dumpKeys = $dumpKeys;
    }
}
