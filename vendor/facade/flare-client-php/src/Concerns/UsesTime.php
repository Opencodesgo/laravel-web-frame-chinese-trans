<?php
/**
 * Facade，FlareClient，关注，使用时间
 */

namespace Facade\FlareClient\Concerns;

use Facade\FlareClient\Time\SystemTime;
use Facade\FlareClient\Time\Time;

trait UsesTime
{
    /** @var \Facade\FlareClient\Time\Time */
    public static $time;

    public static function useTime(Time $time)
    {
        self::$time = $time;
    }

    public function getCurrentTime(): int
    {
        $time = self::$time ?? new SystemTime();

        return $time->getCurrentTime();
    }
}
