<?php
/**
 * Facade，Ignition，解决方案提供者，默认的Db名称解决方案提供者
 */

namespace Facade\Ignition\SolutionProviders;

use Facade\Ignition\Solutions\SuggestUsingCorrectDbNameSolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Illuminate\Database\QueryException;
use Throwable;

class DefaultDbNameSolutionProvider implements HasSolutionsForThrowable
{
    public const MYSQL_UNKNOWN_DATABASE_CODE = 1049;

    public function canSolve(Throwable $throwable): bool
    {
        if (! $throwable instanceof QueryException) {
            return false;
        }

        if ($throwable->getCode() !== self::MYSQL_UNKNOWN_DATABASE_CODE) {
            return false;
        }

        if (! in_array(env('DB_DATABASE'), ['homestead', 'laravel'])) {
            return false;
        }

        return true;
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new SuggestUsingCorrectDbNameSolution()];
    }
}
