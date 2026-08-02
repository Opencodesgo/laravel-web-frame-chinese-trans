<?php
/**
 * Ramsey，Collection，异常，无效排序顺序异常
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

/**
 * Thrown when attempting to access an element out of the range of the collection.
 * 试图访问集合范围之外的元素时抛出。
 */
class OutOfBoundsException extends \OutOfBoundsException
{
}
