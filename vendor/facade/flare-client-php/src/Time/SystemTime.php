<?php
/**
 * Facade，FlareClient，时间，系统时间
 */

namespace Facade\FlareClient\Time;

use DateTimeImmutable;

class SystemTime implements Time
{
    public function getCurrentTime(): int
    {
        return (new DateTimeImmutable())->getTimestamp();
    }
}
