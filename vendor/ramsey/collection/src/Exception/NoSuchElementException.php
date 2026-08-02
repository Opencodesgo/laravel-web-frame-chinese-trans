<?php
/**
 * Ramsey，Collection，异常，无此元素例外
 */

/**
 * This file is part of the ramsey/collection library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Collection\Exception;

use RuntimeException;

/**
 * Thrown when attempting to access an element that does not exist.
 * 试图访问不存在的元素时抛出。
 */
class NoSuchElementException extends RuntimeException
{
}
