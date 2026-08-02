<?php
/**
 * Symfony，Component，HttpFoundation，异常，请求异常接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Exception;

/**
 * Interface for Request exceptions.
 * 请求异常接口
 *
 * Exceptions implementing this interface should trigger an HTTP 400 response in the application code.
 */
interface RequestExceptionInterface
{
}
