<?php
/**
 * Psr，Http，消息，URI 工厂接口
 */

namespace Psr\Http\Message;

interface UriFactoryInterface
{
    /**
     * Create a new URI.
	 * 创建新的URI
     *
     * @param string $uri
     *
     * @return UriInterface
     *
     * @throws \InvalidArgumentException If the given URI cannot be parsed.
     */
    public function createUri(string $uri = ''): UriInterface;
}
