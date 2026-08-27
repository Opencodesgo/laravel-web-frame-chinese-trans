<?php
/**
 * Illuminate，基础，测试，问题，与异常处理交互
 */

namespace Illuminate\Foundation\Testing\Concerns;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Testing\Assert;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait InteractsWithExceptionHandling
{
    /**
     * The original exception handler.
	 * 原始异常处理程序
     *
     * @var \Illuminate\Contracts\Debug\ExceptionHandler|null
     */
    protected $originalExceptionHandler;

    /**
     * Restore exception handling.
	 * 恢复异常处理
     *
     * @return $this
     */
    protected function withExceptionHandling()
    {
        if ($this->originalExceptionHandler) {
            $this->app->instance(ExceptionHandler::class, $this->originalExceptionHandler);
        }

        return $this;
    }

    /**
     * Only handle the given exceptions via the exception handler.
	 * 只通过异常处理程序处理给定的异常
     *
     * @param  array  $exceptions
     * @return $this
     */
    protected function handleExceptions(array $exceptions)
    {
        return $this->withoutExceptionHandling($exceptions);
    }

    /**
     * Only handle validation exceptions via the exception handler.
	 * 只通过异常处理程序处理验证异常
     *
     * @return $this
     */
    protected function handleValidationExceptions()
    {
        return $this->handleExceptions([ValidationException::class]);
    }

    /**
     * Disable exception handling for the test.
	 * 禁用测试的异常处理
     *
     * @param  array  $except
     * @return $this
     */
    protected function withoutExceptionHandling(array $except = [])
    {
        if ($this->originalExceptionHandler == null) {
            $this->originalExceptionHandler = app(ExceptionHandler::class);
        }

        $this->app->instance(ExceptionHandler::class, new class($this->originalExceptionHandler, $except) implements ExceptionHandler
        {
            protected $except;
            protected $originalHandler;

            /**
             * Create a new class instance.
			 * 创建新的类实例
             *
             * @param  \Illuminate\Contracts\Debug\ExceptionHandler  $originalHandler
             * @param  array  $except
             * @return void
             */
            public function __construct($originalHandler, $except = [])
            {
                $this->except = $except;
                $this->originalHandler = $originalHandler;
            }

            /**
             * Report or log an exception.
			 * 报告或记录异常
             *
             * @param  \Throwable  $e
             * @return void
             *
             * @throws \Exception
             */
            public function report(Throwable $e)
            {
                //
            }

            /**
             * Determine if the exception should be reported.
			 * 确定是否应该报告异常
             *
             * @param  \Throwable  $e
             * @return bool
             */
            public function shouldReport(Throwable $e)
            {
                return false;
            }

            /**
             * Render an exception into an HTTP response.
			 * 呈现异常到HTTP响应中
             *
             * @param  \Illuminate\Http\Request  $request
             * @param  \Throwable  $e
             * @return \Symfony\Component\HttpFoundation\Response
             *
             * @throws \Throwable
             */
            public function render($request, Throwable $e)
            {
                foreach ($this->except as $class) {
                    if ($e instanceof $class) {
                        return $this->originalHandler->render($request, $e);
                    }
                }

                if ($e instanceof NotFoundHttpException) {
                    throw new NotFoundHttpException(
                        "{$request->method()} {$request->url()}", $e, is_int($e->getCode()) ? $e->getCode() : 0
                    );
                }

                throw $e;
            }

            /**
             * Render an exception to the console.
			 * 呈现一个异常至控制台
             *
             * @param  \Symfony\Component\Console\Output\OutputInterface  $output
             * @param  \Throwable  $e
             * @return void
             */
            public function renderForConsole($output, Throwable $e)
            {
                (new ConsoleApplication)->renderThrowable($e, $output);
            }
        });

        return $this;
    }

    /**
     * Assert that the given callback throws an exception with the given message when invoked.
	 * 断言给定的回调函数在调用时抛出带有给定消息的异常
     *
     * @param  \Closure  $test
     * @param  class-string<\Throwable>  $expectedClass
     * @param  string|null  $expectedMessage
     * @return $this
     */
    protected function assertThrows(Closure $test, string $expectedClass = Throwable::class, ?string $expectedMessage = null)
    {
        try {
            $test();

            $thrown = false;
        } catch (Throwable $exception) {
            $thrown = $exception instanceof $expectedClass;

            $actualMessage = $exception->getMessage();
        }

        Assert::assertTrue(
            $thrown,
            sprintf('Failed asserting that exception of type "%s" was thrown.', $expectedClass)
        );

        if (isset($expectedMessage)) {
            if (! isset($actualMessage)) {
                Assert::fail(
                    sprintf(
                        'Failed asserting that exception of type "%s" with message "%s" was thrown.',
                        $expectedClass,
                        $expectedMessage
                    )
                );
            } else {
                Assert::assertStringContainsString($expectedMessage, $actualMessage);
            }
        }

        return $this;
    }
}
