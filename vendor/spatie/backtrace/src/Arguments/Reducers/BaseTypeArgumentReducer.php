<?php
/**
 * Spatie，Backtrace，参数，缩减，基类型参数减速器
 */

namespace Spatie\Backtrace\Arguments\Reducers;

use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgument;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\ReducedArgument\UnReducedArgument;

class BaseTypeArgumentReducer implements ArgumentReducer
{
    public function execute($argument): ReducedArgumentContract
    {
        if (is_int($argument)
            || is_float($argument)
            || is_bool($argument)
            || is_string($argument)
            || $argument === null
        ) {
            return new ReducedArgument($argument, get_debug_type($argument));
        }

        return UnReducedArgument::create();
    }
}
