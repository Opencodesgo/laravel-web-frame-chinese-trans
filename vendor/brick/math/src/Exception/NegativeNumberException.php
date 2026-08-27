<?php
/**
 * Brick，Math，异常，负数异常
 */

declare(strict_types=1);

namespace Brick\Math\Exception;

/**
 * Exception thrown when attempting to perform an unsupported operation, such as a square root, on a negative number.
 * 试图对负数执行不受支持的操作（如平方根）时引发的异常。
 */
class NegativeNumberException extends MathException
{
}
