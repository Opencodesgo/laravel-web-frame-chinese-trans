<?php
/**
 * Illuminate，Http，客户端，事件，连接失败
 */

namespace Illuminate\Http\Client\Events;

use Illuminate\Http\Client\Request;

class ConnectionFailed
{
    /**
     * The request instance.
	 * 请求实例
     *
     * @var \Illuminate\Http\Client\Request
     */
    public $request;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
