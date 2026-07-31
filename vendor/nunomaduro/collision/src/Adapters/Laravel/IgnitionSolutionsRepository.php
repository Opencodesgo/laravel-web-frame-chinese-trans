<?php
/**
 * NunoMaduro，Collision，适配器，Laravel，点火解决库
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Adapters\Laravel;

use Facade\IgnitionContracts\SolutionProviderRepository;
use NunoMaduro\Collision\Contracts\SolutionsRepository;
use Throwable;

/**
 * @internal
 */
final class IgnitionSolutionsRepository implements SolutionsRepository
{
    /**
     * Holds an instance of ignition solutions provider repository.
	 * 持有点火解决方案提供商存储库的实例
     *
     * @var \Facade\IgnitionContracts\SolutionProviderRepository
     */
    protected $solutionProviderRepository;

    /**
     * IgnitionSolutionsRepository constructor.
	 * ignitionsolution - srepository构造函数
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
