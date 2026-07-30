<?php
/**
 * GuzzleHttp，处理程序，Curl 工厂接口
 */

namespace GuzzleHttp\Handler;

use Psr\Http\Message\RequestInterface;

interface CurlFactoryInterface
{
    /**
     * Creates a cURL handle resource.
	 * 创建一个cURL句柄资源
     *
     * @param RequestInterface $request Request
     * @param array            $options Transfer options
     *
     * @throws \RuntimeException when an option cannot be applied
     */
    public function create(RequestInterface $request, array $options): EasyHandle;

    /**
     * Release an easy handle, allowing it to be reused or closed.
	 * 释放一个简单句柄，允许它被重用或关闭。
     *
     * This function must call unset on the easy handle's "handle" property.
     */
    public function release(EasyHandle $easy): void;
}
