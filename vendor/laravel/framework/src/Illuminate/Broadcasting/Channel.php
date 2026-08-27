<?php
/**
 * Illuminate，广播，信道，‌信道(Channel)是消息队列(如 RabbitMQ、Kafka)中‌复用物理连接的虚拟传输管道
 */

namespace Illuminate\Broadcasting;

use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;

class Channel
{
    /**
     * The channel's name.
	 * 信道名称
     *
     * @var string
     */
    public $name;

    /**
     * Create a new channel instance.
	 * 创建新的信道实例
     *
     * @param  \Illuminate\Contracts\Broadcasting\HasBroadcastChannel|string  $name
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;
    }

    /**
     * Convert the channel instance to a string.
	 * 将信道实例转换为字符串
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
