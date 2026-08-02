<?php
/**
 * Facade，点火契约，有可行的解决方案吗
 */

namespace Facade\IgnitionContracts;

use Throwable;

interface HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool;

    /** \Facade\IgnitionContracts\Solution[] */
    public function getSolutions(Throwable $throwable): array;
}
