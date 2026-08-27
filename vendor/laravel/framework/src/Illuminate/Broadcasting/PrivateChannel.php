<?php
/**
 * Illuminate，广播，私有信道
 */

namespace Illuminate\Broadcasting;

use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;

class PrivateChannel extends Channel
{
    /**
     * Create a new channel instance.
	 * 创建新的信道实例
     *
     * @param  \Illuminate\Contracts\Broadcasting\HasBroadcastChannel|string  $name
     * @return void
     */
    public function __construct($name)
    {
        $name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;

        parent::__construct('private-'.$name);
    }
}
