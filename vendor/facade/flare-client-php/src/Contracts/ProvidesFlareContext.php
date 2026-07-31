<?php
/**
 * Facade，FlareClient，契约，提供者闪耀上下文
 */

namespace Facade\FlareClient\Contracts;

interface ProvidesFlareContext
{
    public function context(): array;
}
