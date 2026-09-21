<?php
/**
 * Spatie，FlareClient，上下文，上下文提供程序检测器
 */

namespace Spatie\FlareClient\Context;

interface ContextProviderDetector
{
    public function detectCurrentContext(): ContextProvider;
}
