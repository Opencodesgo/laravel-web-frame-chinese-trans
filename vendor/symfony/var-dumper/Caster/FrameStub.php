<?php
/**
 * Symfony，组件，VarDumper，Caster，框架存根
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
 * Represents a single backtrace frame as returned by debug_backtrace() or Exception->getTrace().
 * 表示由debug_backtrace（）或Exception->getTrace（）返回的单个回溯帧。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class FrameStub extends EnumStub
{
    public $keepArgs;
    public $inTraceStub;

    public function __construct(array $frame, bool $keepArgs = true, bool $inTraceStub = false)
    {
        $this->value = $frame;
        $this->keepArgs = $keepArgs;
        $this->inTraceStub = $inTraceStub;
    }
}
