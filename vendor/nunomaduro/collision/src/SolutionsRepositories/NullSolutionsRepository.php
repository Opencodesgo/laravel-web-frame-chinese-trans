<?php
/**
 * NunoMaduro，Collision，解决方案库，零解决库
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
