<?php
/**
 * Illuminate，数据库，Eloquent，可广播模型事件发生
 */

namespace Illuminate\Database\Eloquent;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BroadcastableModelEventOccurred implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * The model instance corresponding to the event.
	 * 与事件对应的模型实例
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * The event name (created, updated, etc.).
	 * 事件名称（创建、更新等）
     *
     * @var string
     */
    protected $event;

    /**
     * The channels that the event should be broadcast on.
	 * 该事件应在哪些频道上播出
     *
     * @var array
     */
    protected $channels = [];

    /**
     * The queue connection that should be used to queue the broadcast job.
	 * 应该用于对广播作业进行排队的队列连接
     *
     * @var string
     */
    public $connection;

    /**
     * The queue that should be used to queue the broadcast job.
	 * 应该用于广播作业排队的队列
     *
     * @var string
     */
    public $queue;

    /**
     * Indicates whether the job should be dispatched after all database transactions have committed.
	 * 指示是否应在所有数据库事务提交后分派作业
     *
     * @var bool|null
     */
    public $afterCommit;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $event
     * @return void
     */
    public function __construct($model, $event)
    {
        $this->model = $model;
        $this->event = $event;
    }

    /**
     * The channels the event should broadcast on.
	 * 应该播放事件的频道
     *
     * @return array
     */
    public function broadcastOn()
    {
        $channels = empty($this->channels)
                ? ($this->model->broadcastOn($this->event) ?: [])
                : $this->channels;

        return collect($channels)->map(function ($channel) {
            return $channel instanceof Model ? new PrivateChannel($channel) : $channel;
        })->all();
    }

    /**
     * The name the event should broadcast as.
	 * 事件应该作为广播的名称
     *
     * @return string
     */
    public function broadcastAs()
    {
        $default = class_basename($this->model).ucfirst($this->event);

        return method_exists($this->model, 'broadcastAs')
                ? ($this->model->broadcastAs($this->event) ?: $default)
                : $default;
    }

    /**
     * Get the data that should be sent with the broadcasted event.
	 * 获取应该随广播事件一起发送的数据
     *
     * @return array|null
     */
    public function broadcastWith()
    {
        return method_exists($this->model, 'broadcastWith')
            ? $this->model->broadcastWith($this->event)
            : null;
    }

    /**
     * Manually specify the channels the event should broadcast on.
	 * 手动指定事件应该广播的频道
     *
     * @param  array  $channels
     * @return $this
     */
    public function onChannels(array $channels)
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * Determine if the event should be broadcast synchronously.
	 * 确定事件是否应该同步广播
     *
     * @return bool
     */
    public function shouldBroadcastNow()
    {
        return $this->event === 'deleted' &&
               ! method_exists($this->model, 'bootSoftDeletes');
    }

    /**
     * Get the event name.
	 * 得到事件名称
     *
     * @return string
     */
    public function event()
    {
        return $this->event;
    }
}
