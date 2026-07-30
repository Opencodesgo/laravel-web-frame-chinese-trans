<?php
/**
 * PhpParser，碰撞，契约，解决方案库
 */

/*
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Contracts;

use Facade\IgnitionContracts\Solution;
use Throwable;

/**
 * This is an Collision Solutions Repository contract.
 * 这是一个冲突解决方案存储库契约。
	 * 
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
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
