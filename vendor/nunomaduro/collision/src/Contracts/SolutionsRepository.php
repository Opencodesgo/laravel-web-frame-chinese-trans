<?php
/**
 * NunoMaduro，Collision，契约，解决方案库
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Contracts;

use Facade\IgnitionContracts\Solution;
use Throwable;

/**
 * @internal
 */
interface SolutionsRepository
{
    /**
     * Gets the solutions from the given `$throwable`.
	 * 从给定的‘ $throwable ’获取解决方案
     *
     * @return array<int, Solution>
     */
    public function getFromThrowable(Throwable $throwable): array;
}
