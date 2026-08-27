<?php
/**
 * GuzzleHttp，处理器，Curl 处理器
 */

namespace GuzzleHttp\Handler;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP handler that uses cURL easy handles as a transport layer.
 * 使用cURL简单句柄作为传输层的HTTP处理程序。
 *
 * When using the CurlHandler, custom curl options can be specified as an
 * associative array of curl option constants mapping to values in the
 * **curl** key of the "client" key of the request.
 *
 * @final
 */
class CurlHandler
{
    /**
     * @var CurlFactoryInterface
     */
    private $factory;

    /**
     * Accepts an associative array of options:
	 * 接受一个选项的关联数组：
     *
     * - handle_factory: Optional curl factory used to create cURL handles.
     *
     * @param array{handle_factory?: ?CurlFactoryInterface} $options Array of options to use with the handler
     */
    public function __construct(array $options = [])
    {
        $this->factory = $options['handle_factory']
            ?? new CurlFactory(3);
    }

    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        if (isset($options['delay'])) {
            \usleep($options['delay'] * 1000);
        }

        $easy = $this->factory->create($request, $options);
        \curl_exec($easy->handle);
        $easy->errno = \curl_errno($easy->handle);

        return CurlFactory::finish($this, $easy, $this->factory);
    }
}
