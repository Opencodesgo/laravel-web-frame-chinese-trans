<?php declare(strict_types=1);

/**
 * PhpParser，错误处理器，收集
 */

namespace PhpParser\ErrorHandler;

use PhpParser\Error;
use PhpParser\ErrorHandler;

/**
 * Error handler that collects all errors into an array.
 * 将所有错误收集到数组中的错误处理程序。
 *
 * This allows graceful handling of errors.
 */
class Collecting implements ErrorHandler {
    /** @var Error[] Collected errors */
    private array $errors = [];

    public function handleError(Error $error): void {
        $this->errors[] = $error;
    }

    /**
     * Get collected errors.
	 * 收集错误
     *
     * @return Error[]
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Check whether there are any errors.
     */
    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    /**
     * Reset/clear collected errors.
     */
    public function clearErrors(): void {
        $this->errors = [];
    }
}
