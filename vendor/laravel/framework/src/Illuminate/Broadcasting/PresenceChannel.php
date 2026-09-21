<?php
/**
 * Illuminate，广播，存在信道
 */

namespace Illuminate\Broadcasting;

class PresenceChannel extends Channel
{
    /**
     * Create a new channel instance.
	 * 创建新的信道实例
     *
     * @param  string  $name
     * @return void
     */
    public function __construct($name)
    {
        parent::__construct('presence-'.$name);
    }
}
