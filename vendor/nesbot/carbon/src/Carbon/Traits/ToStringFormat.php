<?php
/**
 * Carbon，特性，转字符串格式
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Traits;

use Closure;

/**
 * Trait ToStringFormat.
 * ToStringFormat特征
 *
 * Handle global format customization for string cast of the object.
 */
trait ToStringFormat
{
    /**
     * Format to use for __toString method when type juggling occurs.
	 * 当类型杂耍发生时用于__toString方法的格式
     *
     * @var string|Closure|null
     */
    protected static $toStringFormat;

    /**
     * Reset the format used to the default when type juggling a Carbon instance to a string
	 * 将Carbon实例类型转换为字符串时使用的格式重置为默认格式
     *
     * @return void
     */
    public static function resetToStringFormat()
    {
        static::setToStringFormat(null);
    }

    /**
     * @deprecated To avoid conflict between different third-party libraries, static setters should not be used.
     *             You should rather let Carbon object being cast to string with DEFAULT_TO_STRING_FORMAT, and
     *             use other method or custom format passed to format() method if you need to dump another string
     *             format.
     *
     * Set the default format used when type juggling a Carbon instance to a string.
     *
     * @param string|Closure|null $format
     *
     * @return void
     */
    public static function setToStringFormat($format)
    {
        static::$toStringFormat = $format;
    }
}
