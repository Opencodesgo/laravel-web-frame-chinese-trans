<?php
/**
 * Illuminate，契约，广播，有广播信道
 */

namespace Illuminate\Contracts\Broadcasting;

interface HasBroadcastChannel
{
    /**
     * Get the broadcast channel route definition that is associated with the given entity.
	 * 获取与给定实体关联的广播信道路由定义
     *
     * @return string
     */
    public function broadcastChannelRoute();

    /**
     * Get the broadcast channel name that is associated with the given entity.
	 * 获取与给定实体关联的广播信道名称
     *
     * @return string
     */
    public function broadcastChannel();
}
