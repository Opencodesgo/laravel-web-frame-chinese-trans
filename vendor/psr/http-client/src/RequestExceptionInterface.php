<?php
/**
 * Psr，Http，客户端，请求异常接口
 */

namespace Psr\Http\Client;

use Psr\Http\Message\RequestInterface;

/**
 * Exception for when a request failed.
 * 请求失败时的异常
 *
 * Examples:
 *      - Request is invalid (e.g. method is missing)
 *      - Runtime request errors (e.g. the body stream is not seekable)
 */
interface RequestExceptionInterface extends ClientExceptionInterface
{
    /**
     * Returns the request.
	 * 请求返回
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
	 * 请求对象可能与传递给ClientInterface::sendRequest()的对象不同
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;
}
