<?php
/**
 * Illuminate，广播，通道
 */

namespace Illuminate\Broadcasting;

use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;

class Channel
{
    /**
     * The channel's name.
	 * 通道名称
     *
     * @var string
     */
    public $name;

    /**
     * Create a new channel instance.
	 * 创建新的通道
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
	 * 转换通道实例为字符串
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
