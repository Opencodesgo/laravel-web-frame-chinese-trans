<?php
/**
 * Illuminate，通知，事件，创建广播通知
 */

namespace Illuminate\Notifications\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class BroadcastNotificationCreated implements ShouldBroadcast
{
    use Queueable, SerializesModels;

    /**
     * The notifiable entity who received the notification.
	 * 收到通知的应通知实体
     *
     * @var mixed
     */
    public $notifiable;

    /**
     * The notification instance.
	 * 通知实例
     *
     * @var \Illuminate\Notifications\Notification
     */
    public $notification;

    /**
     * The notification data.
	 * 通知数据
     *
     * @var array
     */
    public $data = [];

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @param  array  $data
     * @return void
     */
    public function __construct($notifiable, $notification, $data)
    {
        $this->data = $data;
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
	 * 获取该事件应该播放的频道
     *
     * @return array
     */
    public function broadcastOn()
    {
        if ($this->notifiable instanceof AnonymousNotifiable &&
            $this->notifiable->routeNotificationFor('broadcast')) {
            $channels = Arr::wrap($this->notifiable->routeNotificationFor('broadcast'));
        } else {
            $channels = $this->notification->broadcastOn();
        }

        if (! empty($channels)) {
            return $channels;
        }

        if (is_string($channels = $this->channelName())) {
            return [new PrivateChannel($channels)];
        }

        return collect($channels)->map(function ($channel) {
            return new PrivateChannel($channel);
        })->all();
    }

    /**
     * Get the broadcast channel name for the event.
	 * 获取事件的广播频道名称
     *
     * @return array|string
     */
    protected function channelName()
    {
        if (method_exists($this->notifiable, 'receivesBroadcastNotificationsOn')) {
            return $this->notifiable->receivesBroadcastNotificationsOn($this->notification);
        }

        $class = str_replace('\\', '.', get_class($this->notifiable));

        return $class.'.'.$this->notifiable->getKey();
    }

    /**
     * Get the data that should be sent with the broadcasted event.
	 * 获取应该随广播事件一起发送的数据
     *
     * @return array
     */
    public function broadcastWith()
    {
        if (method_exists($this->notification, 'broadcastWith')) {
            return $this->notification->broadcastWith();
        }

        return array_merge($this->data, [
            'id' => $this->notification->id,
            'type' => $this->broadcastType(),
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
	 * 获取正在广播的通知的类型
     *
     * @return string
     */
    public function broadcastType()
    {
        return method_exists($this->notification, 'broadcastType')
                    ? $this->notification->broadcastType()
                    : get_class($this->notification);
    }
}
