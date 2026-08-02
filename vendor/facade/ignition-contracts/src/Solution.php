<?php
/**
 * Facade，点火契约，方案
 */

namespace Facade\IgnitionContracts;

interface Solution
{
    public function getSolutionTitle(): string;

    public function getSolutionDescription(): string;

    public function getDocumentationLinks(): array;
}
