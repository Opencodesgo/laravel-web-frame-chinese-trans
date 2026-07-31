<?php
/**
 * Illuminate，广播，与广播互动
 */

namespace Illuminate\Broadcasting;

use Illuminate\Support\Arr;

trait InteractsWithBroadcasting
{
    /**
     * The broadcaster connection to use to broadcast the event.
	 * 用于广播事件的广播器连接
     *
     * @var array
     */
    protected $broadcastConnection = [null];

    /**
     * Broadcast the event using a specific broadcaster.
	 * 使用特定广播器广播事件
     *
     * @param  array|string|null  $connection
     * @return $this
     */
    public function broadcastVia($connection = null)
    {
        $this->broadcastConnection = is_null($connection)
                        ? [null]
                        : Arr::wrap($connection);

        return $this;
    }

    /**
     * Get the broadcaster connections the event should be broadcast on.
	 * 获取应该在其上广播事件的广播器连接
     *
     * @return array
     */
    public function broadcastConnections()
    {
        return $this->broadcastConnection;
    }
}
