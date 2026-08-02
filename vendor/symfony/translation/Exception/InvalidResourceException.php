<?php
/**
 * Symfony，Component，Translation，异常，无效资源异常
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
 * Thrown when a resource cannot be loaded.
 * 当资源不能加载时抛出。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class InvalidResourceException extends \InvalidArgumentException implements ExceptionInterface
{
}
