<?php
/**
 * GuzzleHttp，客户端接口
 */

namespace GuzzleHttp;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Client interface for sending HTTP requests.
 * 发送HTTP请求的客户端接口。
 */
interface ClientInterface
{
    /**
     * The Guzzle major version.
	 * Guzzle的主要版本
     */
    public const MAJOR_VERSION = 7;

    /**
     * Send an HTTP request.
	 * 发送HTTP请求
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given
     *                                  request and to the transfer.
     *
     * @throws GuzzleException
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface;

    /**
     * Asynchronously send an HTTP request.
	 * 异步发送HTTP请求
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given
     *                                  request and to the transfer.
     */
    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface;

    /**
     * Create and send an HTTP request.
	 * 创建并发送HTTP请求
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string              $method  HTTP method.
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply.
     *
     * @throws GuzzleException
     */
    public function request(string $method, $uri, array $options = []): ResponseInterface;

    /**
     * Create and send an asynchronous HTTP request.
	 * 创建并发送异步HTTP请求
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well. Use an array to provide a URL
     * template and additional variables to use in the URL template expansion.
     *
     * @param string              $method  HTTP method
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply.
     */
    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface;

    /**
     * Get a client configuration option.
	 * 获取客户端配置选项
     *
     * These options include default request options of the client, a "handler"
     * (if utilized by the concrete client), and a "base_uri" if utilized by
     * the concrete client.
     *
     * @param string|null $option The config option to retrieve.
     *
     * @return mixed
     *
     * @deprecated ClientInterface::getConfig will be removed in guzzlehttp/guzzle:8.0.
     */
    public function getConfig(?string $option = null);
}
