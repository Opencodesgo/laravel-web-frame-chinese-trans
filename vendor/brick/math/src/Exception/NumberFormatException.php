<?php
/**
 * Brick，数学，异常，数字格式异常
 */

declare(strict_types=1);

namespace Brick\Math\Exception;

/**
 * Exception thrown when attempting to create a number from a string with an invalid format.
 * 尝试从格式无效的字符串创建数字时引发的异常。
 */
class NumberFormatException extends MathException
{
    /**
     * @param string $char The failing character.
     *
     * @return NumberFormatException
     *
     * @psalm-pure
     */
    public static function charNotInAlphabet(string $char) : self
    {
        $ord = \ord($char);

        if ($ord < 32 || $ord > 126) {
            $char = \strtoupper(\dechex($ord));

            if ($ord < 10) {
                $char = '0' . $char;
            }
        } else {
            $char = '"' . $char . '"';
        }

        return new self(sprintf('Char %s is not a valid character in the given alphabet.', $char));
    }
}
