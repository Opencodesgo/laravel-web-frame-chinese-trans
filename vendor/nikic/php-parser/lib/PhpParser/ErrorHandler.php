<?php declare(strict_types=1);

/**
 * PhpParser，错误处理器
 */

namespace PhpParser;

interface ErrorHandler {
    /**
     * Handle an error generated during lexing, parsing or some other operation.
	 * 处理词法分析、解析或其他操作期间产生的错误。
     *
     * @param Error $error The error that needs to be handled
     */
    public function handleError(Error $error): void;
}
