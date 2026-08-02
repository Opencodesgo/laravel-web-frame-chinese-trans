<?php
/**
 * Facade，IgnitionContracts，有可行的解决方案
 */

namespace Facade\IgnitionContracts;

use Throwable;

interface HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool;

    /** \Facade\IgnitionContracts\Solution[] */
    public function getSolutions(Throwable $throwable): array;
}
