<?php
/**
 * Carbon，Doctrine，日期和时间默认精度
 */

declare(strict_types=1);

namespace Carbon\Doctrine;

class DateTimeDefaultPrecision
{
    private static $precision = 6;

    /**
     * Change the default Doctrine datetime and datetime_immutable precision.
	 * 更改默认Doctrine datetime和datetime_immutable精度。
     *
     * @param int $precision
     */
    public static function set(int $precision): void
    {
        self::$precision = $precision;
    }

    /**
     * Get the default Doctrine datetime and datetime_immutable precision.
	 * 获取默认Doctrine日期时间和datetime_immutable精度。
     *
     * @return int
     */
    public static function get(): int
    {
        return self::$precision;
    }
}
