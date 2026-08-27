<?php
/**
 * Spatie，LaravelIgnition，解决方案，解决方案提供者，丢失的应用程序密钥解决方案提供者
 */

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use RuntimeException;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Solutions\GenerateAppKeySolution;
use Throwable;

class MissingAppKeySolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        if (! $throwable instanceof RuntimeException) {
            return false;
        }

        return $throwable->getMessage() === 'No application encryption key has been specified.';
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new GenerateAppKeySolution()];
    }
}
