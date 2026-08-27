<?php
/**
 * Illuminate，控制台，线程调度，回调事件
 */

namespace Illuminate\Console\Scheduling;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Reflector;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Throwable;

class CallbackEvent extends Event
{
    /**
     * The callback to call.
	 * 要调用的回调
     *
     * @var string
     */
    protected $callback;

    /**
     * The parameters to pass to the method.
	 * 传递给方法的参数
     *
     * @var array
     */
    protected $parameters;

    /**
     * The result of the callback's execution.
	 * 回调函数执行的结果
     *
     * @var mixed
     */
    protected $result;

    /**
     * The exception that was thrown when calling the callback, if any.
	 * 调用回调时抛出的异常（如果有的话）
     *
     * @var \Throwable|null
     */
    protected $exception;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  \Illuminate\Console\Scheduling\EventMutex  $mutex
     * @param  string|callable  $callback
     * @param  array  $parameters
     * @param  \DateTimeZone|string|null  $timezone
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(EventMutex $mutex, $callback, array $parameters = [], $timezone = null)
    {
        if (! is_string($callback) && ! Reflector::isCallable($callback)) {
            throw new InvalidArgumentException(
                'Invalid scheduled callback event. Must be a string or callable.'
            );
        }

        $this->mutex = $mutex;
        $this->callback = $callback;
        $this->parameters = $parameters;
        $this->timezone = $timezone;
    }

    /**
     * Run the callback event.
	 * 运行回调事件
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return mixed
     *
     * @throws \Throwable
     */
    public function run(Container $container)
    {
        parent::run($container);

        if ($this->exception) {
            throw $this->exception;
        }

        return $this->result;
    }

    /**
     * Determine if the event should skip because another process is overlapping.
	 * 确定是否应该跳过事件，因为另一个进程正在重叠。
     *
     * @return bool
     */
    public function shouldSkipDueToOverlapping()
    {
        return $this->description && parent::shouldSkipDueToOverlapping();
    }

    /**
     * Indicate that the callback should run in the background.
	 * 指示回调应该在后台运行
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function runInBackground()
    {
        throw new RuntimeException('Scheduled closures can not be run in the background.');
    }

    /**
     * Run the callback.
	 * 运行回调
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return int
     */
    protected function execute($container)
    {
        try {
            $this->result = is_object($this->callback)
                ? $container->call([$this->callback, '__invoke'], $this->parameters)
                : $container->call($this->callback, $this->parameters);

            return $this->result === false ? 1 : 0;
        } catch (Throwable $e) {
            $this->exception = $e;

            return 1;
        }
    }

    /**
     * Do not allow the event to overlap each other.
	 * 不要让事件相互重叠
     *
     * The expiration time of the underlying cache lock may be specified in minutes.
     *
     * @param  int  $expiresAt
     * @return $this
     *
     * @throws \LogicException
     */
    public function withoutOverlapping($expiresAt = 1440)
    {
        if (! isset($this->description)) {
            throw new LogicException(
                "A scheduled event name is required to prevent overlapping. Use the 'name' method before 'withoutOverlapping'."
            );
        }

        return parent::withoutOverlapping($expiresAt);
    }

    /**
     * Allow the event to only run on one server for each cron expression.
	 * 对于每个cron表达式，允许事件仅在一台服务器上运行。
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function onOneServer()
    {
        if (! isset($this->description)) {
            throw new LogicException(
                "A scheduled event name is required to only run on one server. Use the 'name' method before 'onOneServer'."
            );
        }

        return parent::onOneServer();
    }

    /**
     * Get the summary of the event for display.
	 * 获取要显示的事件摘要
     *
     * @return string
     */
    public function getSummaryForDisplay()
    {
        if (is_string($this->description)) {
            return $this->description;
        }

        return is_string($this->callback) ? $this->callback : 'Callback';
    }

    /**
     * Get the mutex name for the scheduled command.
	 * 获取计划命令的互斥对象名称
     *
     * @return string
     */
    public function mutexName()
    {
        return 'framework/schedule-'.sha1($this->description ?? '');
    }

    /**
     * Clear the mutex for the event.
	 * 清除事件的互斥锁
     *
     * @return void
     */
    protected function removeMutex()
    {
        if ($this->description) {
            parent::removeMutex();
        }
    }
}
