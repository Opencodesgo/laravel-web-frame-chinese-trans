<?php
/**
 * 门面，Ignition 契约，解决方案提供商存储库
 */

namespace Facade\IgnitionContracts;

use Throwable;

interface SolutionProviderRepository
{
    public function registerSolutionProvider(string $solutionProviderClass): self;

    public function registerSolutionProviders(array $solutionProviderClasses): self;

    /**
     * @param Throwable $throwable
     * @return \Facade\IgnitionContracts\Solution[]
     */
    public function getSolutionsForThrowable(Throwable $throwable): array;

    public function getSolutionForClass(string $solutionClass): ?Solution;
}
