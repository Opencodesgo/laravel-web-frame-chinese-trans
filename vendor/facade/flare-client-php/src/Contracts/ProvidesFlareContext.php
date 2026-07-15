<?php
/**
 * Egulias，Flare Client，上下文，提供者 Flare上下文
 */

namespace Facade\FlareClient\Contracts;

interface ProvidesFlareContext
{
    public function context(): array;
}
