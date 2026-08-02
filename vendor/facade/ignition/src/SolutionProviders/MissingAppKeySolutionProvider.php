<?php
/**
 * Facade，Ignition，解决方案提供者，丢失的 App密钥解决方案提供商
 */

namespace Facade\Ignition\SolutionProviders;

use Facade\Ignition\Solutions\GenerateAppKeySolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use RuntimeException;
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
