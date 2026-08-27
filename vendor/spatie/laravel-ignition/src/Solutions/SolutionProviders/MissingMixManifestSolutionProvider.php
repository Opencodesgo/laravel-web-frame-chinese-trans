<?php
/**
 * Spatie，LaravelIgnition，解决方案，解决方案提供者，缺少混合货单解决方案提供商
 */

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Illuminate\Support\Str;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class MissingMixManifestSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return Str::startsWith($throwable->getMessage(), 'Mix manifest not found');
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [
            BaseSolution::create('Missing Mix Manifest File')
                ->setSolutionDescription('Did you forget to run `npm install && npm run dev`?'),
        ];
    }
}
