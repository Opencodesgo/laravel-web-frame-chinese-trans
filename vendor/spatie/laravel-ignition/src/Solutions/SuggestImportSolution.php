<?php
/**
 * Spatie，LaravelIgnition，解决方案，建议导入解决方案
 */

namespace Spatie\LaravelIgnition\Solutions;

use Spatie\Ignition\Contracts\Solution;

class SuggestImportSolution implements Solution
{
    protected string $class;

    public function __construct(string $class = '')
    {
        $this->class = $class;
    }

    public function getSolutionTitle(): string
    {
        return 'A class import is missing';
    }

    public function getSolutionDescription(): string
    {
        return 'You have a missing class import. Try importing this class: `'.$this->class.'`.';
    }

    public function getDocumentationLinks(): array
    {
        return [];
    }
}
