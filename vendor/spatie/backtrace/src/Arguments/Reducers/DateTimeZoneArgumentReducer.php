<?php
/**
 * Spatie，Backtrace，参数，缩减，时区参数减速器
 */

namespace Spatie\Backtrace\Arguments\Reducers;

use DateTimeZone;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgument;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\ReducedArgument\UnReducedArgument;

class DateTimeZoneArgumentReducer implements ArgumentReducer
{
    public function execute($argument): ReducedArgumentContract
    {
        if (! $argument instanceof DateTimeZone) {
            return UnReducedArgument::create();
        }

        return new ReducedArgument(
            $argument->getName(),
            get_class($argument),
        );
    }
}
