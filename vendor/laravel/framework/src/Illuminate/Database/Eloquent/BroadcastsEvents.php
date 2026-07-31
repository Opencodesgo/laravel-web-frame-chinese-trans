<?php
/**
 * Illuminate，数据库，Eloquent，广播事件
 */

namespace Illuminate\Database\Eloquent;

use Illuminate\Support\Arr;

trait BroadcastsEvents
{
    /**
     * Boot the event broadcasting trait.
	 * 启动事件广播特性
     *
     * @return void
     */
    public static function bootBroadcastsEvents()
    {
        static::created(function ($model) {
            $model->broadcastCreated();
        });

        static::updated(function ($model) {
            $model->broadcastUpdated();
        });

        if (method_exists(static::class, 'bootSoftDeletes')) {
            static::softDeleted(function ($model) {
                $model->broadcastTrashed();
            });

            static::restored(function ($model) {
                $model->broadcastRestored();
            });
        }

        static::deleted(function ($model) {
            $model->broadcastDeleted();
        });
    }

    /**
     * Broadcast that the model was created.
	 * 广播模型已创建
     *
     * @param  \Illuminate\Broadcasting\Channel|\Illuminate\Contracts\Broadcasting\HasBroadcastChannel|array|null  $channels
     * @return \Illuminate\Broadcasting\PendingBroadcast
     */
    public function broadcastCreated($channels = null)
    {
        return $this->broadcastIfBroadcastChannelsExistForEvent(
            $this->newBroadcastableModelEvent('created'), 'created', $channels
        );
    }

    /**
     * Broadcast that the model was updated.
	 * 广播模型已更新
     *
     * @param  \Illuminate\Broadcasting\Channel|\Illuminate\Contracts\Broadcasting\HasBroadcastChannel|array|null  $channels
     * @return \Illuminate\Broadcasting\PendingBroadcast
     */
    public function broadcastUpdated($channels = null)
    {
        return $this->broadcastIfBroadcastChannelsExistForEvent(
            $this->newBroadcastableModelEvent('updated'), 'updated', $channels
        );
    }

    /**
     * Broadcast that the model was trashed.
	 * 广播说模型被毁了
     *
     * @param  \Illuminate\Broadcasting\Channel|\Illuminate\Contracts\Broadcasting\HasBroadcastChannel|array|null  $channels
     * @return \Illuminate\Broadcasting\PendingBroadcast
     */
    public function broadcastTrashed($channels = null)
    {
        return $this->broadcastIfBroadcastChannelsExistForEvent(
            $this->newBroadcastableModelEvent('trashed'), 'trashed', $channels
        );
    }

    /**
     * Broadcast that the model was restored.
	 * 广播模型已恢复
     *
     * @param  \Illuminate\Broadcasting\Channel|\Illuminate\Contracts\Broadcasting\HasBroadcastChannel|array|null  $channels
     * @return \Illuminate\Broadcasting\PendingBroadcast
     */
    public function broadcastRestored($channels = null)
    {
        return $this->broadcastIfBroadcastChannelsExistForEvent(
            $this->newBroadcastableModelEvent('restored'), 'restored', $channels
        );
    }

    /**
     * Broadcast that the model was deleted.
	 * 广播模型已被删除
     *
     * @param  \Illuminate\Broadcasting\Channel|\Illuminate\Contracts\Broadcasting\HasBroadcastChannel|array|null  $channels
     * @return \Illuminate\Broadcasting\PendingBroadcast
     */
    public function broadcastDeleted($channels = null)
    {
        return $this->broadcastIfBroadcastChannelsExistForEvent(
            $this->newBroadcastableModelEvent('deleted'), 'deleted', $channels
        );
    }

    /**
     * Broadcast the given event instance if channels are configured for the model event.
	 * 如果为模型事件配置了通道，则广播给定的事件实例。
     *
     * @param  mixed  $instance
     * @param  string  $event
     * @param  mixed  $channels
     * @return \Illuminate\Broadcasting\PendingBroadcast|null
     */
    protected function broadcastIfBroadcastChannelsExistForEvent($instance, $event, $channels = null)
    {
        if (! static::$isBroadcasting) {
            return;
        }

        if (! empty($this->broadcastOn($event)) || ! empty($channels)) {
            return broadcast($instance->onChannels(Arr::wrap($channels)));
        }
    }

    /**
     * Create a new broadcastable model event event.
	 * 创建一个新的可广播模型事件事件
     *
     * @param  string  $event
     * @return mixed
     */
    public function newBroadcastableModelEvent($event)
    {
        return tap($this->newBroadcastableEvent($event), function ($event) {
            $event->connection = property_exists($this, 'broadcastConnection')
                            ? $this->broadcastConnection
                            : $this->broadcastConnection();

            $event->queue = property_exists($this, 'broadcastQueue')
                            ? $this->broadcastQueue
                            : $this->broadcastQueue();

            $event->afterCommit = property_exists($this, 'broadcastAfterCommit')
                            ? $this->broadcastAfterCommit
                            : $this->broadcastAfterCommit();
        });
    }

    /**
     * Create a new broadcastable model event for the model.
	 * 为模型创建一个新的可广播模型事件
     *
     * @param  string  $event
     * @return \Illuminate\Database\Eloquent\BroadcastableModelEventOccurred
     */
    protected function newBroadcastableEvent($event)
    {
        return new BroadcastableModelEventOccurred($this, $event);
    }

    /**
     * Get the channels that model events should broadcast on.
	 * 获取模型事件应该广播的通道
     *
     * @param  string  $event
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn($event)
    {
        return [$this];
    }

    /**
     * Get the queue connection that should be used to broadcast model events.
	 * 获取应该用于广播模型事件的队列连接
     *
     * @return string|null
     */
    public function broadcastConnection()
    {
        //
    }

    /**
     * Get the queue that should be used to broadcast model events.
	 * 获取应用于广播模型事件的队列
     *
     * @return string|null
     */
    public function broadcastQueue()
    {
        //
    }

    /**
     * Determine if the model event broadcast queued job should be dispatched after all transactions are committed.
	 * 确定是否应该在提交所有事务后分派模型事件广播排队作业
     *
     * @return bool
     */
    public function broadcastAfterCommit()
    {
        return false;
    }
}
