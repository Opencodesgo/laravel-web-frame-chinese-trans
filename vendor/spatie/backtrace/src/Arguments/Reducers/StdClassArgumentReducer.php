<?php
/**
 * Spatie，Backtrace，参数，缩减，StdClass 参数减速器
 */

namespace Spatie\Backtrace\Arguments\Reducers;

use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\ReducedArgument\UnReducedArgument;
use stdClass;

class StdClassArgumentReducer extends ArrayArgumentReducer
{
    public function execute($argument): ReducedArgumentContract
    {
        if (! $argument instanceof stdClass) {
            return UnReducedArgument::create();
        }

        return parent::reduceArgument((array) $argument, stdClass::class);
    }
}
