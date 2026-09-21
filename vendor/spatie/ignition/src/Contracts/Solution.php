<?php
/**
 * Spatie，Ignition，契约，解决方案
 */

namespace Spatie\Ignition\Contracts;

interface Solution
{
    public function getSolutionTitle(): string;

    public function getSolutionDescription(): string;

    /** @return array<string, string> */
    public function getDocumentationLinks(): array;
}
