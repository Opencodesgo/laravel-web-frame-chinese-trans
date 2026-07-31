<?php
/**
 * Psr，Http，客户端，网络异常接口
 */

namespace Psr\Http\Client;

use Psr\Http\Message\RequestInterface;

/**
 * Thrown when the request cannot be completed because of network issues.
 * 当请求不能通过网络问题完成时抛出。
 *
 * There is no response object as this exception is thrown when no response has been received.
 *
 * Example: the target host name can not be resolved or the connection failed.
 */
interface NetworkExceptionInterface extends ClientExceptionInterface
{
    /**
     * Returns the request.
	 * 返回请求
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;
}
