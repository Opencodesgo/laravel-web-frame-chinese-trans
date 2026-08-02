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

use RuntimeException;

/**
 * Thrown when attempting to use a sort order that is not recognized.
 * 试图使用无法识别的排序顺序时抛出。
 */
class InvalidSortOrderException extends RuntimeException
{
}
