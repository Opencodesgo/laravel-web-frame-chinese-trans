<?php
/**
 * Illuminate，控制台，信号
 */

namespace Illuminate\Console;

/**
 * @internal
 */
class Signals
{
    /**
     * The signal registry instance.
	 * 信号注册实例
     *
     * @var \Symfony\Component\Console\SignalRegistry\SignalRegistry
     */
    protected $registry;

    /**
     * The signal registry's previous list of handlers.
	 * 信号注册表先前的处理程序列表
     *
     * @var array<int, array<int, callable>>|null
     */
    protected $previousHandlers;

    /**
     * The current availability resolver, if any.
	 * 当前可用性解析器（如果有的话）
     *
     * @var (callable(): bool)|null
     */
    protected static $availabilityResolver;

    /**
     * Create a new signal registrar instance.
	 * 创建一个新的信号注册器实例
     *
     * @param  \Symfony\Component\Console\SignalRegistry\SignalRegistry  $registry
     * @return void
     */
    public function __construct($registry)
    {
        $this->registry = $registry;

        $this->previousHandlers = $this->getHandlers();
    }

    /**
     * Register a new signal handler.
	 * 注册一个新的信号处理程序
     *
     * @param  int  $signal
     * @param  callable(int $signal): void  $callback
     * @return void
     */
    public function register($signal, $callback)
    {
        $this->previousHandlers[$signal] ??= $this->initializeSignal($signal);

        with($this->getHandlers(), function ($handlers) use ($signal) {
            $handlers[$signal] ??= $this->initializeSignal($signal);

            $this->setHandlers($handlers);
        });

        $this->registry->register($signal, $callback);

        with($this->getHandlers(), function ($handlers) use ($signal) {
            $lastHandlerInserted = array_pop($handlers[$signal]);

            array_unshift($handlers[$signal], $lastHandlerInserted);

            $this->setHandlers($handlers);
        });
    }

    /**
     * Gets the signal's existing handler in array format.
	 * 以数组格式获取信号的现有处理程序
     *
     * @return array<int, callable(int $signal): void>
     */
    protected function initializeSignal($signal)
    {
        return is_callable($existingHandler = pcntl_signal_get_handler($signal))
            ? [$existingHandler]
            : null;
    }

    /**
     * Unregister the current signal handlers.
	 * 注销当前的信号处理程序
     *
     * @return array<int, array<int, callable(int $signal): void>>
     */
    public function unregister()
    {
        $previousHandlers = $this->previousHandlers;

        foreach ($previousHandlers as $signal => $handler) {
            if (is_null($handler)) {
                pcntl_signal($signal, SIG_DFL);

                unset($previousHandlers[$signal]);
            }
        }

        $this->setHandlers($previousHandlers);
    }

    /**
     * Execute the given callback if "signals" should be used and are available.
	 * 如果应该使用"signals"并且可用，则执行给定的回调。
     *
     * @param  callable  $callback
     * @return void
     */
    public static function whenAvailable($callback)
    {
        $resolver = static::$availabilityResolver;

        if ($resolver()) {
            $callback();
        }
    }

    /**
     * Get the registry's handlers.
	 * 获取注册中心的处理程序
     *
     * @return array<int, array<int, callable>>
     */
    protected function getHandlers()
    {
        return (fn () => $this->signalHandlers)
            ->call($this->registry);
    }

    /**
     * Set the registry's handlers.
	 * 设置注册中心的处理程序
     *
     * @param  array<int, array<int, callable(int $signal):void>>  $handlers
     * @return void
     */
    protected function setHandlers($handlers)
    {
        (fn () => $this->signalHandlers = $handlers)
            ->call($this->registry);
    }

    /**
     * Set the availability resolver.
	 * 设置可用性解析器
     *
     * @param  callable(): bool
     * @return void
     */
    public static function resolveAvailabilityUsing($resolver)
    {
        static::$availabilityResolver = $resolver;
    }
}
