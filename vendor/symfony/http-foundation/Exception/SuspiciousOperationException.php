<?php
/**
 * Symfony，Component，HttpFoundation，异常，可疑操作异常
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
 * Raised when a user has performed an operation that should be considered
 * suspicious from a security perspective.
 * 当用户执行一个应该被考虑的操作时提出
 */
class SuspiciousOperationException extends \UnexpectedValueException implements RequestExceptionInterface
{
}
