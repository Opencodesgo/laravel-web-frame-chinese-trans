<?php
/**
 * Facade，IgnitionContracts，可运行的解决方案
 */

namespace Facade\IgnitionContracts;

interface RunnableSolution extends Solution
{
    public function getSolutionActionDescription(): string;

    public function getRunButtonText(): string;

    public function run(array $parameters = []);

    public function getRunParameters(): array;
}
