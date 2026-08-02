<?php
/**
 * Facade，IgnitionContracts，解决方案
 */

namespace Facade\IgnitionContracts;

interface Solution
{
    public function getSolutionTitle(): string;

    public function getSolutionDescription(): string;

    public function getDocumentationLinks(): array;
}
