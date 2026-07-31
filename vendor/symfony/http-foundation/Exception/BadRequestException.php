<?php
/**
 * Symfony，Component，HttpFoundation，异常，错误请求异常
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
 * Raised when a user sends a malformed request.
 * 当用户发送格式错误的请求时引发
 */
class BadRequestException extends \UnexpectedValueException implements RequestExceptionInterface
{
}
