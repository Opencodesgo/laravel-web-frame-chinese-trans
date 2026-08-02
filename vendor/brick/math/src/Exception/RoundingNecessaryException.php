<?php
/**
 * Brick，Math，异常，舍入必要异常
 */

declare(strict_types=1);

namespace Brick\Math\Exception;

/**
 * Exception thrown when a number cannot be represented at the requested scale without rounding.
 * 当一个数字不能在请求的尺度上表示时抛出异常。
 */
class RoundingNecessaryException extends MathException
{
    /**
     * @return RoundingNecessaryException
     *
     * @psalm-pure
     */
    public static function roundingNecessary() : RoundingNecessaryException
    {
        return new self('Rounding is necessary to represent the result of the operation at this scale.');
    }
}
