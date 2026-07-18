<?php
/**
 * Dotenv，异常，无效路径异常
 */

namespace Dotenv\Exception;

use InvalidArgumentException;

/**
 * This is the invalid path exception class.
 */
class InvalidPathException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
