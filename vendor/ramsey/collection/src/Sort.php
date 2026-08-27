<?php
/**
 * Ramsey，集合，排序
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

namespace Ramsey\Collection;

/**
 * Collection sorting
 * 分类收集
 */
enum Sort: string
{
    /**
     * Sort items in a collection in ascending order.
     */
    case Ascending = 'asc';

    /**
     * Sort items in a collection in descending order.
     */
    case Descending = 'desc';
}
