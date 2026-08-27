<?php
/**
 * NunoMaduro，Collision，适配器，Laravel，点火解决方案库
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Adapters\Laravel;

use NunoMaduro\Collision\Contracts\SolutionsRepository;
use Spatie\Ignition\Contracts\SolutionProviderRepository;
use Throwable;

/**
 * @internal
 */
final class IgnitionSolutionsRepository implements SolutionsRepository
{
    /**
     * Holds an instance of ignition solutions provider repository.
	 * 保存一个点火解决方案提供商存储库的实例。
     *
     * @var \Spatie\Ignition\Contracts\SolutionProviderRepository
     */
    protected $solutionProviderRepository;

    /**
     * IgnitionSolutionsRepository constructor.
	 * IgnitionSolutionsRepository 构造函数
     */
    public function __construct(SolutionProviderRepository $solutionProviderRepository)
    {
        $this->solutionProviderRepository = $solutionProviderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromThrowable(Throwable $throwable): array
    {
        return $this->solutionProviderRepository->getSolutionsForThrowable($throwable);
    }
}
