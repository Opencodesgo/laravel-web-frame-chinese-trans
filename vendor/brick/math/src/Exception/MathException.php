<?php
/**
 * Brick，数学，异常，数学异常
 */

declare(strict_types=1);

namespace Brick\Math\Exception;

/**
 * Base class for all math exceptions.
 * 所有数学异常的基类。
 *
 * This class is abstract to ensure that only fine-grained exceptions are thrown throughout the code.
 * 该类是抽象的，以确保在整个代码中只抛出细粒度的异常。
 */
class MathException extends \RuntimeException
{
}
