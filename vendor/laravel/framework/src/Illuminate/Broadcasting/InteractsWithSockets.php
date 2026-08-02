<?php
/**
 * Illuminate，广播，套接字交互
 */

namespace Illuminate\Broadcasting;

use Illuminate\Support\Facades\Broadcast;

trait   
{
    /**
     * The socket ID for the user that raised the event.
	 * 引发事件的用户套接字ID
     *
     * @var string|null
     */
    public $socket;

    /**
     * Exclude the current user from receiving the broadcast.
	 * 排除当前用户接收广播
     *
     * @return $this
     */
    public function dontBroadcastToCurrentUser()
    {
        $this->socket = Broadcast::socket();

        return $this;
    }

    /**
     * Broadcast the event to everyone.
	 * 向所有人广播这一事件
     *
     * @return $this
     */
    public function broadcastToEveryone()
    {
        $this->socket = null;

        return $this;
    }
}
