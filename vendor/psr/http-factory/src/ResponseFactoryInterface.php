<?php
/**
 * Psr，Http，消息，响应工厂接口
 */

namespace Psr\Http\Message;

interface ResponseFactoryInterface
{
    /**
     * Create a new response.
	 * 创建新的响应
     *
     * @param int $code HTTP status code; defaults to 200
     * @param string $reasonPhrase Reason phrase to associate with status code
     *     in generated response; if none is provided implementations MAY use
     *     the defaults as suggested in the HTTP specification.
     *
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface;
}
