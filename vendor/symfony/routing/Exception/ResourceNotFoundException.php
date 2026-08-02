<?php
/**
 * Symfony，Component，Routing，异常，资源未找到异常
 */

/*
 * This file is part of the Symfony package.
 * 该文件是Symfony包的一部分
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Exception;

/**
 * The resource was not found.
 * 找不到资源
 *
 * This exception should trigger an HTTP 404 response in your application code.
 * 此异常应该在应用程序代码中触发HTTP 404响应。
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class ResourceNotFoundException extends \RuntimeException implements ExceptionInterface
{
}
