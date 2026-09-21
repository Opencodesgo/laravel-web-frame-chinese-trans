<?php
/**
 * Spatie，FlareClient，上下文，上下文提供程序
 */

namespace Spatie\FlareClient\Context;

interface ContextProvider
{
    /**
     * @return array<int, string|mixed>
     */
    public function toArray(): array;
}
