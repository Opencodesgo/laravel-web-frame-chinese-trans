<?php
/**
 * Psr，Http，客户端，网络异常接口
 */

namespace Psr\Http\Client;

use Psr\Http\Message\RequestInterface;

/**
 * Thrown when the request cannot be completed because of network issues.
 * 当由于网络问题而无法完成请求时抛出
 *
 * There is no response object as this exception is thrown when no response has been received.
 * 没有响应对象，因为当没有收到响应时将引发此异常。
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
