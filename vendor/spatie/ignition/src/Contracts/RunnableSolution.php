<?php
/**
 * Spatie，Ignition，契约，可运行解决方案
 */

namespace Spatie\Ignition\Contracts;

interface RunnableSolution extends Solution
{
    public function getSolutionActionDescription(): string;

    public function getRunButtonText(): string;

    /** @param array<string, mixed> $parameters */
    public function run(array $parameters = []): void;

    /** @return array<string, mixed> */
    public function getRunParameters(): array;
}
