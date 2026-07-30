<?php
/**
 * Ramsey，Collection，异常，集合不匹配异常
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
 * Thrown when attempting to operate on collections of differing types.
 * 试图操作不同类型的集合时抛出。
 */
class CollectionMismatchException extends RuntimeException
{
}
