<?php
/**
 * Illuminate，契约，支持，消息提供者
 */

namespace Illuminate\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
	 * 获取实例的消息
     *
     * @return \Illuminate\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
