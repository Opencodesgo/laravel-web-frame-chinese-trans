<?php
/**
 * GuzzleHttp，异常，不良响应异常
 */

namespace GuzzleHttp\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Exception when an HTTP error occurs (4xx or 5xx error)
 * 当HTTP错误发生时异常(4xx或5xx错误)
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
	 * 当前异常和扩展它将总是有响应
     */
    public function hasResponse(): bool
    {
        return true;
    }

    /**
     * This function narrows the return type from the parent class and does not allow it to be nullable.
	 * 这个函数将返回类型从父类缩小,不允许它无效。
     */
    public function getResponse(): ResponseInterface
    {
        /** @var ResponseInterface */
        return parent::getResponse();
    }
}
