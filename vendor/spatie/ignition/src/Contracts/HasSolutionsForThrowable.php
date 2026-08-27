<?php
/**
 * Spatie，Ignition，契约，可扔物品有解决方案吗
 */

namespace Spatie\Ignition\Contracts;

use Throwable;

/**
 * Interface used for SolutionProviders.
 * 用于解决方案提供商的接口。
 */
interface HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool;

    /** @return array<int, \Spatie\Ignition\Contracts\Solution> */
    public function getSolutions(Throwable $throwable): array;
}
