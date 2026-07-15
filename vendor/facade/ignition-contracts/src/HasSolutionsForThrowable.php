<?php
/**
 * 门面，Ignition 契约，可扔物品有解决方案吗
 */

namespace Facade\IgnitionContracts;

use Throwable;

interface HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool;

    /** \Facade\IgnitionContracts\Solution[] */
    public function getSolutions(Throwable $throwable): array;
}
