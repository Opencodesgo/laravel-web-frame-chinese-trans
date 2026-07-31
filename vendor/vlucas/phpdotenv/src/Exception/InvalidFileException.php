<?php
/**
 * Dotenv，异常，无效文件异常
 */

declare(strict_types=1);

namespace Dotenv\Exception;

use InvalidArgumentException;

final class InvalidFileException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
