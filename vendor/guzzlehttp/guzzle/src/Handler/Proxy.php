<?php
/**
 * GuzzleHttp，处理器，代理
 */

namespace GuzzleHttp\Handler;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;

/**
 * Provides basic proxies for handlers.
 * 为处理程序提供基本代理。
 *
 * @final
 */
class Proxy
{
    /**
     * Sends synchronous requests to a specific handler while sending all other
     * requests to another handler.
	 * 向特定处理器发送同步请求，同时将所有其他请求发送到另一个处理器。
     *
     * @param callable(RequestInterface, array): PromiseInterface $default Handler used for normal responses
     * @param callable(RequestInterface, array): PromiseInterface $sync    Handler used for synchronous responses.
     *
     * @return callable(RequestInterface, array): PromiseInterface Returns the composed handler.
     */
    public static function wrapSync(callable $default, callable $sync): callable
    {
        return static function (RequestInterface $request, array $options) use ($default, $sync): PromiseInterface {
            return empty($options[RequestOptions::SYNCHRONOUS]) ? $default($request, $options) : $sync($request, $options);
        };
    }

    /**
     * Sends streaming requests to a streaming compatible handler while sending
     * all other requests to a default handler.
	 * 将流式请求发送到支持流式的处理程序，而将所有其他请求发送到默认处理程序。
     *
     * This, for example, could be useful for taking advantage of the
     * performance benefits of curl while still supporting true streaming
     * through the StreamHandler.
     *
     * @param callable(RequestInterface, array): PromiseInterface $default   Handler used for non-streaming responses
     * @param callable(RequestInterface, array): PromiseInterface $streaming Handler used for streaming responses
     *
     * @return callable(RequestInterface, array): PromiseInterface Returns the composed handler.
     */
    public static function wrapStreaming(callable $default, callable $streaming): callable
    {
        return static function (RequestInterface $request, array $options) use ($default, $streaming): PromiseInterface {
            return empty($options['stream']) ? $default($request, $options) : $streaming($request, $options);
        };
    }
}
