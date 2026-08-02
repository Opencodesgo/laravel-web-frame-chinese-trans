<?php
/**
 * Symfony，Component，HttpKernel，异常，Http 异常接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Exception;

/**
 * Interface for HTTP error exceptions.
 * HTTP 错误异常接口
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
interface HttpExceptionInterface extends \Throwable
{
    /**
     * Returns the status code.
	 * 返回状态码
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Returns response headers.
	 * 返回响应头
     *
     * @return array
     */
    public function getHeaders();
}
