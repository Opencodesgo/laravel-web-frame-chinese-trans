<?php
/**
 * Illuminate，契约，广播，应该广播
 */

namespace Illuminate\Contracts\Broadcasting;

interface ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
	 * 得到应该广播的事件信道
     *
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]|string[]|string
     */
    public function broadcastOn();
}
