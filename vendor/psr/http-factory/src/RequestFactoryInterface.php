<?php
/**
 * Psr，Http，消息，请求工厂接口
 */

namespace Psr\Http\Message;

interface RequestFactoryInterface
{
    /**
     * Create a new request.
	 * 创建新的请求
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request. If
     *     the value is a string, the factory MUST create a UriInterface
     *     instance based on it.
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface;
}
