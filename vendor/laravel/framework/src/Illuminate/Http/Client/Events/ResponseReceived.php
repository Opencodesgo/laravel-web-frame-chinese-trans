<?php
/**
 * Illuminate，Http，客户端，事件，响应接收
 */

namespace Illuminate\Http\Client\Events;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;

class ResponseReceived
{
    /**
     * The request instance.
	 * 请求实例
     *
     * @var \Illuminate\Http\Client\Request
     */
    public $request;

    /**
     * The response instance.
	 * 响应实例
     *
     * @var \Illuminate\Http\Client\Response
     */
    public $response;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @param  \Illuminate\Http\Client\Response  $response
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
