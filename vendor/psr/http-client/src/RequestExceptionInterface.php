<?php
/**
 * Psr，Http，客户端，请求异常接口
 */

namespace Psr\Http\Client;

use Psr\Http\Message\RequestInterface;

/**
 * Exception for when a request failed.
 * 当请求失败时,异常。
 *
 * Examples:
 *      - Request is invalid (e.g. method is missing)
 *      - Runtime request errors (e.g. the body stream is not seekable)
 */
interface RequestExceptionInterface extends ClientExceptionInterface
{
    /**
     * Returns the request.
	 * 返回请求
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
	 * 请求对象可能与传递给ClientInterface::sendRequest（）的对象不同。
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;
}
