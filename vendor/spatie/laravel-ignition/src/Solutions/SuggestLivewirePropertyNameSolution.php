<?php
/**
 * Spatie，LaravelIgnition，解决方案，建议Livewire 属性名称解决方案
 */

namespace Spatie\LaravelIgnition\Solutions;

use Spatie\Ignition\Contracts\Solution;

class SuggestLivewirePropertyNameSolution implements Solution
{
    public function __construct(
        protected string $variableName,
        protected string $componentClass,
        protected string $suggested,
    ) {
    }

    public function getSolutionTitle(): string
    {
        return "Possible typo {$this->variableName}";
    }

    public function getDocumentationLinks(): array
    {
        return [];
    }

    public function getSolutionDescription(): string
    {
        return "Did you mean `$this->suggested`?";
    }

    public function isRunnable(): bool
    {
        return false;
    }
}
