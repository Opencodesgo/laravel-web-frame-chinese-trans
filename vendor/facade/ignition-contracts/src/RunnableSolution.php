<?php
/**
 * 门面，Ignition 契约，可运行解决方案
 */

namespace Facade\IgnitionContracts;

interface RunnableSolution extends Solution
{
    public function getSolutionActionDescription(): string;

    public function getRunButtonText(): string;

    public function run(array $parameters = []);

    public function getRunParameters(): array;
}
