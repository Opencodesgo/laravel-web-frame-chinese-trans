<?php
/**
 * Dotenv，异常，无效路径异常
 */

declare(strict_types=1);

namespace Dotenv\Exception;

use InvalidArgumentException;

final class InvalidPathException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
