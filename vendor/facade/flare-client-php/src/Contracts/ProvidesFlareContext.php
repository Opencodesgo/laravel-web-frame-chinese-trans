<?php
/**
 * Facade，FlareClient，契约，提供 Flare背景信息
 */

namespace Facade\FlareClient\Contracts;

interface ProvidesFlareContext
{
    public function context(): array;
}
