<?php
/**
 * Brick，Math，异常，舍入必要的异常
 */

declare(strict_types=1);

namespace Brick\Math\Exception;

/**
 * Exception thrown when a number cannot be represented at the requested scale without rounding.
 * 当数字不能在不舍入的情况下以请求的比例表示时引发的异常。
 */
class RoundingNecessaryException extends MathException
{
    /**
     * @psalm-pure
     */
    public static function roundingNecessary() : RoundingNecessaryException
    {
        return new self('Rounding is necessary to represent the result of the operation at this scale.');
    }
}
