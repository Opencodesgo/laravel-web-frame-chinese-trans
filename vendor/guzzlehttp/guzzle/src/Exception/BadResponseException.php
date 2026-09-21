<?php
/**
 * GuzzleHttp，异常，不良响应异常
 */

namespace GuzzleHttp\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Exception when an HTTP error occurs (4xx or 5xx error)
 * HTTP错误发生时的异常（4xx或5xx错误）
 */
class BadResponseException extends RequestException
{
    public function __construct(
        string $message,
        RequestInterface $request,
        ResponseInterface $response,
        ?\Throwable $previous = null,
        array $handlerContext = []
    ) {
        parent::__construct($message, $request, $response, $previous, $handlerContext);
    }

    /**
     * Current exception and the ones that extend it will always have a response.
	 * 当前异常和扩展它的异常总是有一个响应
     */
    public function hasResponse(): bool
    {
        return true;
    }

    /**
     * This function narrows the return type from the parent class and does not allow it to be nullable.
	 * 此函数缩小父类的返回类型，并且不允许返回类型为空。
     */
    public function getResponse(): ResponseInterface
    {
        /** @var ResponseInterface */
        return parent::getResponse();
    }
}
