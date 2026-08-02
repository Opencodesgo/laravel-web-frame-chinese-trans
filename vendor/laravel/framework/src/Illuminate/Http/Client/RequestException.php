<?php
/**
 * Illuminate，Http，客户端，请求异常
 */

namespace Illuminate\Http\Client;

class RequestException extends HttpClientException
{
    /**
     * The response instance.
	 * 响应实例
     *
     * @var \Illuminate\Http\Client\Response
     */
    public $response;

    /**
     * Create a new exception instance.
	 * 创建新的异常实例
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return void
     */
    public function __construct(Response $response)
    {
        parent::__construct("HTTP request returned status code {$response->status()}.", $response->status());

        $this->response = $response;
    }
}
