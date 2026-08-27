<?php
/**
 * Spatie，Backtrace，参数，缩减，减少参数
 */

namespace Spatie\Backtrace\Arguments\Reducers;

use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;

interface ArgumentReducer
{
    /**
     * @param mixed $argument
     */
    public function execute($argument): ReducedArgumentContract;
}
