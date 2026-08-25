<?php
/**
 * Ramsey，Collection，异常，值提取异常
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
 * Thrown when attempting to extract a value for a method or property that does not exist.
 * 试图提取不存在的方法或属性的值时抛出。
 */
class ValueExtractionException extends RuntimeException
{
}
