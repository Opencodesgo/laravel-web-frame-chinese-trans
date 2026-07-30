<?php
/**
 * Brick，数学，异常，零除异常
 */

declare(strict_types=1);

namespace Brick\Math\Exception;

/**
 * Exception thrown when a division by zero occurs.
 * 当一个分区发生时,异常会发生。
 */
class DivisionByZeroException extends MathException
{
    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function divisionByZero() : DivisionByZeroException
    {
        return new self('Division by zero.');
    }

    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function modulusMustNotBeZero() : DivisionByZeroException
    {
        return new self('The modulus must not be zero.');
    }

    /**
     * @return DivisionByZeroException
     *
     * @psalm-pure
     */
    public static function denominatorMustNotBeZero() : DivisionByZeroException
    {
        return new self('The denominator of a rational number cannot be zero.');
    }
}
