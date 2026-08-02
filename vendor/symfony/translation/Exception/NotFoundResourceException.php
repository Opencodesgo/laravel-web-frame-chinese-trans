<?php
/**
 * Symfony，组件，翻译，异常，未找到资源异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Exception;

/**
 * Thrown when a resource does not exist.
 * 当资源不存在时抛出。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class NotFoundResourceException extends \InvalidArgumentException implements ExceptionInterface
{
}
