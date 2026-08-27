<?php
/**
 * Brick，Math，异常，除零异常
 */

declare(strict_types=1);

namespace Brick\Math\Exception;

/**
 * Exception thrown when a division by zero occurs.
 * 发生除零时引发的异常
 */
class DivisionByZeroException extends MathException
{
    /**
     * @psalm-pure
     */
    public static function divisionByZero() : DivisionByZeroException
    {
        return new self('Division by zero.');
    }

    /**
     * @psalm-pure
     */
    public static function modulusMustNotBeZero() : DivisionByZeroException
    {
        return new self('The modulus must not be zero.');
    }

    /**
     * @psalm-pure
     */
    public static function denominatorMustNotBeZero() : DivisionByZeroException
    {
        return new self('The denominator of a rational number cannot be zero.');
    }
}
