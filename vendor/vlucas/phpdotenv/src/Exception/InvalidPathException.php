<?php
/**
 * Dotenv，异常，无效路径异常
 */

namespace Dotenv\Exception;

use InvalidArgumentException;

class InvalidPathException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
