<?php
/**
 * Dotenv，异常，无效文件异常
 */

namespace Dotenv\Exception;

use InvalidArgumentException;

class InvalidFileException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
