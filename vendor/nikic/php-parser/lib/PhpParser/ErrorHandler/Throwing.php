<?php declare(strict_types=1);

/**
 * PhpParser，错误处理程序，Throwing
 */

namespace PhpParser\ErrorHandler;

use PhpParser\Error;
use PhpParser\ErrorHandler;

/**
 * Error handler that handles all errors by throwing them.
 * 通过抛出错误来处理所有错误的错误处理程序。
 *
 * This is the default strategy used by all components.
 */
class Throwing implements ErrorHandler {
    public function handleError(Error $error): void {
        throw $error;
    }
}
