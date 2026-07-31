<?php
/**
 * Dotenv，异常，无效编码异常
 */

declare(strict_types=1);

namespace Dotenv\Exception;

use InvalidArgumentException;

final class InvalidEncodingException extends InvalidArgumentException implements ExceptionInterface
{
    //
}
