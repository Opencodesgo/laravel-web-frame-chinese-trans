<?php
/**
 * PhpParser，碰撞，解决方案存储库，Null解决方案存储库
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\SolutionsRepositories;

use NunoMaduro\Collision\Contracts\SolutionsRepository;
use Throwable;

/**
 * This is an Collision Null Solutions Provider implementation.
 * 这是一个碰撞Null解决方案提供程序的实现。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class NullSolutionsRepository implements SolutionsRepository
{
    /**
     * {@inheritdoc}
     */
    public function getFromThrowable(Throwable $throwable): array
    {
        return [];
    }
}
