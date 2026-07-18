<?php
/**
 * Symfony，契约，HTTP客户端，异常，客户端异常接口
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

/**
 * When a 4xx response is returned.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface ClientExceptionInterface extends HttpExceptionInterface
{
}
