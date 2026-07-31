<?php
/**
 * Symfony，Component，HttpFoundation，异常，Json 异常
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
 * Thrown by Request::toArray() when the content cannot be JSON-decoded.
 * 当内容不能被json解码时，由Request::toArray()抛出。
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class JsonException extends \UnexpectedValueException implements RequestExceptionInterface
{
}
