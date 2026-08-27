<?php
/**
 * Ramsey，集合，数组接口
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

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * `ArrayInterface` provides traversable array functionality to data types.
 * ‘ ArrayInterface ’为数据类型提供了可遍历的数组功能。
 *
 * @template T
 * @extends ArrayAccess<array-key, T>
 * @extends IteratorAggregate<array-key, T>
 */
interface ArrayInterface extends
    ArrayAccess,
    Countable,
    IteratorAggregate
{
    /**
     * Removes all items from this array.
	 * 从该数组中删除所有项
     */
    public function clear(): void;

    /**
     * Returns a native PHP array representation of this array object.
	 * 返回此数组对象的本机PHP数组表示形式
     *
     * @return array<array-key, T>
     */
    public function toArray(): array;

    /**
     * Returns `true` if this array is empty.
	 * 如果此数组为空，则返回‘ true ’。
     */
    public function isEmpty(): bool;
}
