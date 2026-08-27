<?php
/**
 * Spatie，FlareClient，契约，提供者 Flare上下文
 */

namespace Spatie\FlareClient\Contracts;

interface ProvidesFlareContext
{
    /**
     * @return array<int|string, mixed>
     */
    public function context(): array;
}
