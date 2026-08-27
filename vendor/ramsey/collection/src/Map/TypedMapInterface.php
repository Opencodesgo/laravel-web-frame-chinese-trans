<?php
/**
 * Ramsey，集合，映射，类型化映射接口
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

namespace Ramsey\Collection\Map;

/**
 * A `TypedMapInterface` represents a map of elements where key and value are
 * typed.
 *
 * @template K of array-key
 * @template T
 * @extends MapInterface<K, T>
 */
interface TypedMapInterface extends MapInterface
{
    /**
     * Return the type used on the key.
	 * 返回键上使用的类型
     */
    public function getKeyType(): string;

    /**
     * Return the type forced on the values.
	 * 返回值的强制类型
     */
    public function getValueType(): string;
}
