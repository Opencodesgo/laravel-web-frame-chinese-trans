<?php
/**
 * Psr，Clock，时钟接口
 */

namespace Psr\Clock;

use DateTimeImmutable;

interface ClockInterface
{
    /**
     * Returns the current time as a DateTimeImmutable Object
	 * 作为DateTimeImmutable对象返回当前时间
     */
    public function now(): DateTimeImmutable;
}
