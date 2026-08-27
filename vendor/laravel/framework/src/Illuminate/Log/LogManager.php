<?php
/**
 * Illuminate，日志，日志管理器
 */

namespace Illuminate\Log;

use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @mixin \Illuminate\Log\Logger
 */
class LogManager implements LoggerInterface
{
    use ParsesLogConfiguration;

    /**
     * The application instance.
	 * 程序实例
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved channels.
	 * 已解析通道的数组
     *
     * @var array
     */
    protected $channels = [];

    /**
     * The context shared across channels and stacks.
	 * 跨通道和堆栈共享的上下文
     *
     * @var array
     */
    protected $sharedContext = [];

    /**
     * The registered custom driver creators.
	 * 注册的自定义驱动程序创建者
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * The standard date format to use when writing logs.
	 * 编写日志时使用的标准日期格式
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Create a new Log manager instance.
	 * 创建一个新的日志管理器实例
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Build an on-demand log channel.
	 * 构建按需日志通道
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    public function build(array $config)
    {
        unset($this->channels['ondemand']);

        return $this->get('ondemand', $config);
    }

    /**
     * Create a new, on-demand aggregate logger instance.
	 * 创建一个新的按需聚合日志记录器实例
     *
     * @param  array  $channels
     * @param  string|null  $channel
     * @return \Psr\Log\LoggerInterface
     */
    public function stack(array $channels, $channel = null)
    {
        return (new Logger(
            $this->createStackDriver(compact('channels', 'channel')),
            $this->app['events']
        ))->withContext($this->sharedContext);
    }

    /**
     * Get a log channel instance.
	 * 获取日志通道实例
     *
     * @param  string|null  $channel
     * @return \Psr\Log\LoggerInterface
     */
    public function channel($channel = null)
    {
        return $this->driver($channel);
    }

    /**
     * Get a log driver instance.
	 * 获取日志驱动程序实例
     *
     * @param  string|null  $driver
     * @return \Psr\Log\LoggerInterface
     */
    public function driver($driver = null)
    {
        return $this->get($this->parseDriver($driver));
    }

    /**
     * Attempt to get the log from the local cache.
	 * 尝试从本地缓存中获取日志
     *
     * @param  string  $name
     * @param  array|null  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function get($name, ?array $config = null)
    {
        try {
            return $this->channels[$name] ?? with($this->resolve($name, $config), function ($logger) use ($name) {
                return $this->channels[$name] = $this->tap($name, new Logger($logger, $this->app['events']))->withContext($this->sharedContext);
            });
        } catch (Throwable $e) {
            return tap($this->createEmergencyLogger(), function ($logger) use ($e) {
                $logger->emergency('Unable to create configured logger. Using emergency logger.', [
                    'exception' => $e,
                ]);
            });
        }
    }

    /**
     * Apply the configured taps for the logger.
	 * 为记录器应用配置的水龙头
     *
     * @param  string  $name
     * @param  \Illuminate\Log\Logger  $logger
     * @return \Illuminate\Log\Logger
     */
    protected function tap($name, Logger $logger)
    {
        foreach ($this->configurationFor($name)['tap'] ?? [] as $tap) {
            [$class, $arguments] = $this->parseTap($tap);

            $this->app->make($class)->__invoke($logger, ...explode(',', $arguments));
        }

        return $logger;
    }

    /**
     * Parse the given tap class string into a class name and arguments string.
	 * 将给定的tap类字符串解析为类名和参数字符串
     *
     * @param  string  $tap
     * @return array
     */
    protected function parseTap($tap)
    {
        return str_contains($tap, ':') ? explode(':', $tap, 2) : [$tap, ''];
    }

    /**
     * Create an emergency log handler to avoid white screens of death.
	 * 创建紧急日志处理程序以避免白屏死机
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createEmergencyLogger()
    {
        $config = $this->configurationFor('emergency');

        $handler = new StreamHandler(
            $config['path'] ?? $this->app->storagePath().'/logs/laravel.log',
            $this->level(['level' => 'debug'])
        );

        return new Logger(
            new Monolog('laravel', $this->prepareHandlers([$handler])),
            $this->app['events']
        );
    }

    /**
     * Resolve the given log instance by name.
	 * 按名称解析给定的日志实例
     *
     * @param  string  $name
     * @param  array|null  $config
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name, ?array $config = null)
    {
        $config ??= $this->configurationFor($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Log [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
    }

    /**
     * Call a custom driver creator.
	 * 调用自定义驱动程序创建者
     *
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Create a custom log driver instance.
	 * 创建自定义日志驱动程序实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createCustomDriver(array $config)
    {
        $factory = is_callable($via = $config['via']) ? $via : $this->app->make($via);

        return $factory($config);
    }

    /**
     * Create an aggregate log driver instance.
	 * 创建聚合日志驱动程序实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createStackDriver(array $config)
    {
        if (is_string($config['channels'])) {
            $config['channels'] = explode(',', $config['channels']);
        }

        $handlers = collect($config['channels'])->flatMap(function ($channel) {
            return $channel instanceof LoggerInterface
                ? $channel->getHandlers()
                : $this->channel($channel)->getHandlers();
        })->all();

        $processors = collect($config['channels'])->flatMap(function ($channel) {
            return $channel instanceof LoggerInterface
                ? $channel->getProcessors()
                : $this->channel($channel)->getProcessors();
        })->all();

        if ($config['ignore_exceptions'] ?? false) {
            $handlers = [new WhatFailureGroupHandler($handlers)];
        }

        return new Monolog($this->parseChannel($config), $handlers, $processors);
    }

    /**
     * Create an instance of the single file log driver.
	 * 创建单个文件日志驱动程序的实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSingleDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(
                new StreamHandler(
                    $config['path'], $this->level($config),
                    $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
                ), $config
            ),
        ]);
    }

    /**
     * Create an instance of the daily file log driver.
	 * 创建每日文件日志驱动程序的实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createDailyDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new RotatingFileHandler(
                $config['path'], $config['days'] ?? 7, $this->level($config),
                $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
            ), $config),
        ]);
    }

    /**
     * Create an instance of the Slack log driver.
	 * 创建Slack日志驱动程序的实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSlackDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new SlackWebhookHandler(
                $config['url'],
                $config['channel'] ?? null,
                $config['username'] ?? 'Laravel',
                $config['attachment'] ?? true,
                $config['emoji'] ?? ':boom:',
                $config['short'] ?? false,
                $config['context'] ?? true,
                $this->level($config),
                $config['bubble'] ?? true,
                $config['exclude_fields'] ?? []
            ), $config),
        ]);
    }

    /**
     * Create an instance of the syslog log driver.
	 * 创建syslog日志驱动程序的实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSyslogDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new SyslogHandler(
                Str::snake($this->app['config']['app.name'], '-'),
                $config['facility'] ?? LOG_USER, $this->level($config)
            ), $config),
        ]);
    }

    /**
     * Create an instance of the "error log" log driver.
	 * 创建"错误日志"日志驱动程序的实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     */
    protected function createErrorlogDriver(array $config)
    {
        return new Monolog($this->parseChannel($config), [
            $this->prepareHandler(new ErrorLogHandler(
                $config['type'] ?? ErrorLogHandler::OPERATING_SYSTEM, $this->level($config)
            )),
        ]);
    }

    /**
     * Create an instance of any handler available in Monolog.
	 * 创建一个在独白中可用的任何处理程序的实例
     *
     * @param  array  $config
     * @return \Psr\Log\LoggerInterface
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function createMonologDriver(array $config)
    {
        if (! is_a($config['handler'], HandlerInterface::class, true)) {
            throw new InvalidArgumentException(
                $config['handler'].' must be an instance of '.HandlerInterface::class
            );
        }

        $with = array_merge(
            ['level' => $this->level($config)],
            $config['with'] ?? [],
            $config['handler_with'] ?? []
        );

        return new Monolog($this->parseChannel($config), [$this->prepareHandler(
            $this->app->make($config['handler'], $with), $config
        )]);
    }

    /**
     * Prepare the handlers for usage by Monolog.
	 * 准备处理程序供Monolog使用
     *
     * @param  array  $handlers
     * @return array
     */
    protected function prepareHandlers(array $handlers)
    {
        foreach ($handlers as $key => $handler) {
            $handlers[$key] = $this->prepareHandler($handler);
        }

        return $handlers;
    }

    /**
     * Prepare the handler for usage by Monolog.
	 * 准备处理程序供独白使用
     *
     * @param  \Monolog\Handler\HandlerInterface  $handler
     * @param  array  $config
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function prepareHandler(HandlerInterface $handler, array $config = [])
    {
        if (isset($config['action_level'])) {
            $handler = new FingersCrossedHandler(
                $handler,
                $this->actionLevel($config),
                0,
                true,
                $config['stop_buffering'] ?? true
            );
        }

        if (! $handler instanceof FormattableHandlerInterface) {
            return $handler;
        }

        if (! isset($config['formatter'])) {
            $handler->setFormatter($this->formatter());
        } elseif ($config['formatter'] !== 'default') {
            $handler->setFormatter($this->app->make($config['formatter'], $config['formatter_with'] ?? []));
        }

        return $handler;
    }

    /**
     * Get a Monolog formatter instance.
	 * 获取一个独白格式化程序实例
     *
     * @return \Monolog\Formatter\FormatterInterface
     */
    protected function formatter()
    {
        return tap(new LineFormatter(null, $this->dateFormat, true, true), function ($formatter) {
            $formatter->includeStacktraces();
        });
    }

    /**
     * Share context across channels and stacks.
	 * 跨通道和栈共享上下文
     *
     * @param  array  $context
     * @return $this
     */
    public function shareContext(array $context)
    {
        foreach ($this->channels as $channel) {
            $channel->withContext($context);
        }

        $this->sharedContext = array_merge($this->sharedContext, $context);

        return $this;
    }

    /**
     * The context shared across channels and stacks.
	 * 跨通道和堆栈共享的上下文
     *
     * @return array
     */
    public function sharedContext()
    {
        return $this->sharedContext;
    }

    /**
     * Flush the shared context.
	 * 刷新共享上下文
     *
     * @return $this
     */
    public function flushSharedContext()
    {
        $this->sharedContext = [];

        return $this;
    }

    /**
     * Get fallback log channel name.
	 * 获取回退日志通道名称
     *
     * @return string
     */
    protected function getFallbackChannelName()
    {
        return $this->app->bound('env') ? $this->app->environment() : 'production';
    }

    /**
     * Get the log connection configuration.
	 * 获取日志连接配置
     *
     * @param  string  $name
     * @return array
     */
    protected function configurationFor($name)
    {
        return $this->app['config']["logging.channels.{$name}"];
    }

    /**
     * Get the default log driver name.
	 * 获取默认的日志驱动程序名称
     *
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['logging.default'];
    }

    /**
     * Set the default log driver name.
	 * 设置默认的日志驱动程序名称
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['logging.default'] = $name;
    }

    /**
     * Register a custom driver creator Closure.
	 * 注册自定义驱动程序创建器Closure
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Unset the given channel instance.
	 * 取消给定通道实例的设置
     *
     * @param  string|null  $driver
     * @return void
     */
    public function forgetChannel($driver = null)
    {
        $driver = $this->parseDriver($driver);

        if (isset($this->channels[$driver])) {
            unset($this->channels[$driver]);
        }
    }

    /**
     * Parse the driver name.
	 * 解析驱动程序名称
     *
     * @param  string|null  $driver
     * @return string|null
     */
    protected function parseDriver($driver)
    {
        $driver ??= $this->getDefaultDriver();

        if ($this->app->runningUnitTests()) {
            $driver ??= 'null';
        }

        return $driver;
    }

    /**
     * Get all of the resolved log channels.
	 * 获取所有已解析的日志通道
     *
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * System is unusable.
	 * 系统不可用
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->driver()->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
	 * 必须立即采取行动
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->driver()->alert($message, $context);
    }

    /**
     * Critical conditions.
	 * 临界状态
     *
     * Example: Application component unavailable, unexpected exception.
	 * 示例：应用程序组件不可用，意外异常。
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->driver()->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->driver()->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
	 * 不属于错误的异常情况
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->driver()->warning($message, $context);
    }

    /**
     * Normal but significant events.
	 * 正常但重要的事件
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->driver()->notice($message, $context);
    }

    /**
     * Interesting events.
	 * 有趣的事件
     *
     * Example: User logs in, SQL logs.
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->driver()->info($message, $context);
    }

    /**
     * Detailed debug information.
	 * 详细的调试信息
     *
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->driver()->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
	 * 具有任意级别的日志
     *
     * @param  mixed  $level
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->driver()->log($level, $message, $context);
    }

    /**
     * Dynamically call the default driver instance.
	 * 动态调用默认驱动程序实例
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
