<?php
/**
 * Facade，Ignition，解决方案，建议 Livewire属性名称解决方案
 */

namespace Facade\Ignition\Solutions;

use Facade\IgnitionContracts\Solution;

class SuggestLivewirePropertyNameSolution implements Solution
{
    /** @var string */
    private $variableName;

    /** @var string */
    private $componentClass;

    /** @var string|null */
    private $suggested;

    public function __construct($variableName = null, $componentClass = null, $suggested = null)
    {
        $this->variableName = $variableName;
        $this->componentClass = $componentClass;
        $this->suggested = $suggested;
    }

    public function getSolutionTitle(): string
    {
        return "Possible typo {$this->componentClass}::{$this->variableName}";
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
