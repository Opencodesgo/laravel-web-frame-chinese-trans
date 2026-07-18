<?php
/**
 * Symfony，契约，HTTP客户端，异常，Http 异常接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Contracts\HttpClient\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Base interface for HTTP-related exceptions.
 *
 * @author Anton Chernikov <anton_ch1989@mail.ru>
 */
interface HttpExceptionInterface extends ExceptionInterface
{
    public function getResponse(): ResponseInterface;
}
