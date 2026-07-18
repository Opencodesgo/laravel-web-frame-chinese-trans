<?php
/**
 * Dotenv，异常，无效文件异常
 */

namespace Dotenv\Exception;

use InvalidArgumentException;

/**
 * This is the invalid file exception class.
 * 这是无效的文件异常类。
 */
class InvalidFileException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
