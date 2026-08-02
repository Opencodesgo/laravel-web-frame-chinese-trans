<?php
/**
 * Facade，FlareClient，上下文，上下文探测器接口
 */

namespace Facade\FlareClient\Context;

interface ContextDetectorInterface
{
    public function detectCurrentContext(): ContextInterface;
}
