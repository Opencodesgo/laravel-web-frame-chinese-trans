<?php
/**
 * Illuminate，广播，广播事件
 */

namespace Illuminate\Broadcasting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastingFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionProperty;

class BroadcastEvent implements ShouldQueue
{
    use Queueable;

    /**
     * The event instance.
	 * 事件实例
     *
     * @var mixed
     */
    public $event;

    /**
     * The number of times the job may be attempted.
	 * 可能尝试该作业的次数
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds the job can run before timing out.
	 * 作业在超时之前可以运行的秒数
     *
     * @var int
     */
    public $timeout;

    /**
     * The number of seconds to wait before retrying the job when encountering an uncaught exception.
	 * 遇到未捕获的异常时，在重新尝试作业之前等待的秒数
     *
     * @var int
     */
    public $backoff;

    /**
     * Create a new job handler instance.
	 * 创建一个新的作业处理程序实例
     *
     * @param  mixed  $event
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
        $this->tries = property_exists($event, 'tries') ? $event->tries : null;
        $this->timeout = property_exists($event, 'timeout') ? $event->timeout : null;
        $this->backoff = property_exists($event, 'backoff') ? $event->backoff : null;
        $this->afterCommit = property_exists($event, 'afterCommit') ? $event->afterCommit : null;
    }

    /**
     * Handle the queued job.
	 * 处理排队作业
     *
     * @param  \Illuminate\Contracts\Broadcasting\Factory  $manager
     * @return void
     */
    public function handle(BroadcastingFactory $manager)
    {
        $name = method_exists($this->event, 'broadcastAs')
                ? $this->event->broadcastAs() : get_class($this->event);

        $channels = Arr::wrap($this->event->broadcastOn());

        if (empty($channels)) {
            return;
        }

        $connections = method_exists($this->event, 'broadcastConnections')
                            ? $this->event->broadcastConnections()
                            : [null];

        $payload = $this->getPayloadFromEvent($this->event);

        foreach ($connections as $connection) {
            $manager->connection($connection)->broadcast(
                $channels, $name, $payload
            );
        }
    }

    /**
     * Get the payload for the given event.
	 * 获取给定事件的有效负载
     *
     * @param  mixed  $event
     * @return array
     */
    protected function getPayloadFromEvent($event)
    {
        if (method_exists($event, 'broadcastWith') &&
            ! is_null($payload = $event->broadcastWith())) {
            return array_merge($payload, ['socket' => data_get($event, 'socket')]);
        }

        $payload = [];

        foreach ((new ReflectionClass($event))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $payload[$property->getName()] = $this->formatProperty($property->getValue($event));
        }

        unset($payload['broadcastQueue']);

        return $payload;
    }

    /**
     * Format the given value for a property.
	 * 为属性设置给定值的格式
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function formatProperty($value)
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        return $value;
    }

    /**
     * Get the display name for the queued job.
	 * 获取排队作业的显示名称
     *
     * @return string
     */
    public function displayName()
    {
        return get_class($this->event);
    }

    /**
     * Prepare the instance for cloning.
	 * 为克隆准备实例
     *
     * @return void
     */
    public function __clone()
    {
        $this->event = clone $this->event;
    }
}
