<?php
/**
 * Dotenv，异常，验证异常
 */

declare(strict_types=1);

namespace Dotenv\Exception;

use RuntimeException;

final class ValidationException extends RuntimeException implements ExceptionInterface
{
    //
}
