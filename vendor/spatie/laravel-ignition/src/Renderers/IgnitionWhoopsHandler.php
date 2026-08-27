<?php
/**
 * Spatie，LaravelIgnition，渲染器，点火Whoops 处理程序
 */

namespace Spatie\LaravelIgnition\Renderers;

use Error;
use ErrorException;
use Throwable;
use Whoops\Handler\Handler;

class IgnitionWhoopsHandler extends Handler
{
    protected ErrorPageRenderer $errorPageHandler;

    protected Throwable $exception;

    public function __construct(ErrorPageRenderer $errorPageHandler)
    {
        $this->errorPageHandler = $errorPageHandler;
    }

    public function handle(): ?int
    {
        try {
            $this->errorPageHandler->render($this->exception);
        } catch (Error $error) {
            // Errors aren't caught by Whoops.
            // Convert the error to an exception and throw again.
			// 错误不会被Whoops捕获。

            throw new ErrorException(
                $error->getMessage(),
                $error->getCode(),
                1,
                $error->getFile(),
                $error->getLine(),
                $error
            );
        }

        return Handler::QUIT;
    }

    /** @param \Throwable $exception */
    public function setException($exception): void
    {
        $this->exception = $exception;
    }
}
