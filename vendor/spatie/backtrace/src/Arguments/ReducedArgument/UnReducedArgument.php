<?php
/**
 * Spatie，Backtrace，参数，减少参数，未约简论证
 */

namespace Spatie\Backtrace\Arguments\ReducedArgument;

class UnReducedArgument implements ReducedArgumentContract
{
    /** @var self|null */
    private static $instance = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        return self::$instance = new self();
    }
}
