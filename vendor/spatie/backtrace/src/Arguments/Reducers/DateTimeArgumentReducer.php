<?php
/**
 * Spatie，Backtrace，参数，缩减，时间参数呈现
 */

namespace Spatie\Backtrace\Arguments\Reducers;

use DateTimeInterface;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgument;
use Spatie\Backtrace\Arguments\ReducedArgument\ReducedArgumentContract;
use Spatie\Backtrace\Arguments\ReducedArgument\UnReducedArgument;

class DateTimeArgumentReducer implements ArgumentReducer
{
    public function execute($argument): ReducedArgumentContract
    {
        if (! $argument instanceof DateTimeInterface) {
            return UnReducedArgument::create();
        }

        return new ReducedArgument(
            $argument->format('d M Y H:i:s e'),
            get_class($argument),
        );
    }
}
