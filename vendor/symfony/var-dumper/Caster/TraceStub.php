<?php
/**
 * Symfony，Component，VarDumper，Caster，Trace Stub
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
 * Represents a backtrace as returned by debug_backtrace() or Exception->getTrace().
 * 表示由debug_backtrace（）或Exception->getTrace（）返回的回溯。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class TraceStub extends Stub
{
    public $keepArgs;
    public $sliceOffset;
    public $sliceLength;
    public $numberingOffset;

    public function __construct(array $trace, bool $keepArgs = true, int $sliceOffset = 0, ?int $sliceLength = null, int $numberingOffset = 0)
    {
        $this->value = $trace;
        $this->keepArgs = $keepArgs;
        $this->sliceOffset = $sliceOffset;
        $this->sliceLength = $sliceLength;
        $this->numberingOffset = $numberingOffset;
    }
}
