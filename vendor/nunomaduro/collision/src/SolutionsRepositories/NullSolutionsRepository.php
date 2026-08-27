<?php
/**
 * NunoMaduro，Collision，异常，解决方案存储库，Null 解决方案存储库
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\SolutionsRepositories;

use NunoMaduro\Collision\Contracts\SolutionsRepository;
use Throwable;

/**
 * @internal
 */
final class NullSolutionsRepository implements SolutionsRepository
{
    /**
     * {@inheritdoc}
     */
    public function getFromThrowable(Throwable $throwable): array
    {
        return [];
    }
}
