<?php
/**
 * Illuminate，基础，引导，处理异常
 */

namespace Illuminate\Foundation\Bootstrap;

use ErrorException;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\LogManager;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\SocketHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

class HandleExceptions
{
    /**
     * Reserved memory so that errors can be displayed properly on memory exhaustion.
	 * 预留内存，以便在内存耗尽时正确显示错误。
     *
     * @var string|null
     */
    public static $reservedMemory;

    /**
     * The application instance.
	 * 应用实例
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected static $app;

    /**
     * Bootstrap the given application.
	 * 引导给定的应用程序
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        self::$reservedMemory = str_repeat('x', 32768);

        static::$app = $app;

        error_reporting(-1);

        set_error_handler($this->forwardsTo('handleError'));

        set_exception_handler($this->forwardsTo('handleException'));

        register_shutdown_function($this->forwardsTo('handleShutdown'));

        if (! $app->environment('testing')) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * Report PHP deprecations, or convert PHP errors to ErrorException instances.
	 * 报告PHP弃用，或将PHP错误转换为ErrorException实例。
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if ($this->isDeprecation($level)) {
            $this->handleDeprecationError($message, $file, $line, $level);
        } elseif (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Reports a deprecation to the "deprecations" logger.
	 * 向"deprecations"日志记录器报告弃用。
     *
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @return void
     *
     * @deprecated Use handleDeprecationError instead.
     */
    public function handleDeprecation($message, $file, $line)
    {
        $this->handleDeprecationError($message, $file, $line);
    }

    /**
     * Reports a deprecation to the "deprecations" logger.
	 * 向"deprecations"日志记录器报告弃用
     *
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  int  $level
     * @return void
     */
    public function handleDeprecationError($message, $file, $line, $level = E_DEPRECATED)
    {
        if ($this->shouldIgnoreDeprecationErrors()) {
            return;
        }

        try {
            $logger = static::$app->make(LogManager::class);
        } catch (Exception $e) {
            return;
        }

        $this->ensureDeprecationLoggerIsConfigured();

        $options = static::$app['config']->get('logging.deprecations') ?? [];

        with($logger->channel('deprecations'), function ($log) use ($message, $file, $line, $level, $options) {
            if ($options['trace'] ?? false) {
                $log->warning((string) new ErrorException($message, 0, $level, $file, $line));
            } else {
                $log->warning(sprintf('%s in %s on line %s',
                    $message, $file, $line
                ));
            }
        });
    }

    /**
     * Determine if deprecation errors should be ignored.
	 * 确定是否应该忽略弃用错误
     *
     * @return bool
     */
    protected function shouldIgnoreDeprecationErrors()
    {
        return ! class_exists(LogManager::class)
            || ! static::$app->hasBeenBootstrapped()
            || static::$app->runningUnitTests();
    }

    /**
     * Ensure the "deprecations" logger is configured.
	 * 确保配置了"deprecations"日志记录器。
     *
     * @return void
     */
    protected function ensureDeprecationLoggerIsConfigured()
    {
        with(static::$app['config'], function ($config) {
            if ($config->get('logging.channels.deprecations')) {
                return;
            }

            $this->ensureNullLogDriverIsConfigured();

            if (is_array($options = $config->get('logging.deprecations'))) {
                $driver = $options['channel'] ?? 'null';
            } else {
                $driver = $options ?? 'null';
            }

            $config->set('logging.channels.deprecations', $config->get("logging.channels.{$driver}"));
        });
    }

    /**
     * Ensure the "null" log driver is configured.
	 * 确保配置了"null"日志驱动程序
     *
     * @return void
     */
    protected function ensureNullLogDriverIsConfigured()
    {
        with(static::$app['config'], function ($config) {
            if ($config->get('logging.channels.null')) {
                return;
            }

            $config->set('logging.channels.null', [
                'driver' => 'monolog',
                'handler' => NullHandler::class,
            ]);
        });
    }

    /**
     * Handle an uncaught exception from the application.
	 * 处理应用程序中未捕获的异常
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function handleException(Throwable $e)
    {
        self::$reservedMemory = null;

        try {
            $this->getExceptionHandler()->report($e);
        } catch (Exception $e) {
            $exceptionHandlerFailed = true;
        }

        if (static::$app->runningInConsole()) {
            $this->renderForConsole($e);

            if ($exceptionHandlerFailed ?? false) {
                exit(1);
            }
        } else {
            $this->renderHttpResponse($e);
        }
    }

    /**
     * Render an exception to the console.
	 * 向控制台呈现一个异常
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderForConsole(Throwable $e)
    {
        $this->getExceptionHandler()->renderForConsole(new ConsoleOutput, $e);
    }

    /**
     * Render an exception as an HTTP response and send it.
	 * 将异常呈现为HTTP响应并发送
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderHttpResponse(Throwable $e)
    {
        $this->getExceptionHandler()->render(static::$app['request'], $e)->send();
    }

    /**
     * Handle the PHP shutdown event.
	 * 处理PHP关闭事件
     *
     * @return void
     */
    public function handleShutdown()
    {
        self::$reservedMemory = null;

        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalErrorFromPhpError($error, 0));
        }
    }

    /**
     * Create a new fatal error instance from an error array.
	 * 从错误数组创建新的致命错误实例
     *
     * @param  array  $error
     * @param  int|null  $traceOffset
     * @return \Symfony\Component\ErrorHandler\Error\FatalError
     */
    protected function fatalErrorFromPhpError(array $error, $traceOffset = null)
    {
        return new FatalError($error['message'], 0, $error, $traceOffset);
    }

    /**
     * Forward a method call to the given method if an application instance exists.
	 * 如果存在应用程序实例，则转发对给定方法的方法调用。
     *
     * @return callable
     */
    protected function forwardsTo($method)
    {
        return fn (...$arguments) => static::$app
            ? $this->{$method}(...$arguments)
            : false;
    }

    /**
     * Determine if the error level is a deprecation.
	 * 确定错误级别是否为弃用
     *
     * @param  int  $level
     * @return bool
     */
    protected function isDeprecation($level)
    {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    /**
     * Determine if the error type is fatal.
	 * 确定错误类型是否致命
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

    /**
     * Get an instance of the exception handler.
	 * 获取异常处理程序的实例
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected function getExceptionHandler()
    {
        return static::$app->make(ExceptionHandler::class);
    }

    /**
     * Clear the local application instance from memory.
	 * 从内存中清除本地应用程序实例
     *
     * @return void
     */
    public static function forgetApp()
    {
        static::$app = null;
    }
}
