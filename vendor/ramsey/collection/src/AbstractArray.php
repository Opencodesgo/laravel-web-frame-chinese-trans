<?php
/**
 * Ramsey，Collection，抽象数组
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

use ArrayIterator;
use Traversable;

use function count;
use function serialize;
use function unserialize;

/**
 * This class provides a basic implementation of `ArrayInterface`, to minimize
 * the effort required to implement this interface.
 * 这个类提供了‘ ArrayInterface ’的基本实现，实现此接口所需的工作量。
 *
 * @template T
 * @implements ArrayInterface<T>
 */
abstract class AbstractArray implements ArrayInterface
{
    /**
     * The items of this array.
	 * 此数组的项
     *
     * @var array<array-key, T>
     */
    protected array $data = [];

    /**
     * Constructs a new array object.
	 * 构造一个新的数组对象
     *
     * @param array<array-key, T> $data The initial items to add to this array.
     */
    public function __construct(array $data = [])
    {
        // Invoke offsetSet() for each value added; in this way, sub-classes
        // may provide additional logic about values added to the array object.
        foreach ($data as $key => $value) {
            $this[$key] = $value;
        }
    }

    /**
     * Returns an iterator for this array.
	 * 返回此数组的迭代器
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php IteratorAggregate::getIterator()
     *
     * @return Traversable<array-key, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Returns `true` if the given offset exists in this array.
	 * 如果给定的偏移量存在于此数组中，则返回‘ true ’。
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     *
     * @param array-key $offset The offset to check.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * Returns the value at the specified offset.
	 * 返回指定偏移量处的值
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     *
     * @param array-key $offset The offset for which a value should be returned.
     *
     * @return T|null the value stored at the offset, or null if the offset
     *     does not exist.
     */
    #[\ReturnTypeWillChange] // phpcs:ignore
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * Sets the given value to the given offset in the array.
	 * 将给定值设置为数组中的给定偏移量
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     *
     * @param array-key|null $offset The offset to set. If `null`, the value may be
     *     set at a numerically-indexed offset.
     * @param T $value The value to set at the given offset.
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Removes the given offset and its value from the array.
	 * 从数组中移除给定的偏移量及其值
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     *
     * @param array-key $offset The offset to remove from the array.
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Returns a serialized string representation of this array object.
	 * 返回此数组对象的序列化字符串表示形式
     *
     * @deprecated The Serializable interface will go away in PHP 9.
     *
     * @link http://php.net/manual/en/serializable.serialize.php Serializable::serialize()
     *
     * @return string a PHP serialized string.
     */
    public function serialize(): string
    {
        return serialize($this->data);
    }

    /**
     * Returns data suitable for PHP serialization.
	 * 返回适合于PHP序列化的数据
     *
     * @link https://www.php.net/manual/en/language.oop5.magic.php#language.oop5.magic.serialize
     * @link https://www.php.net/serialize
     *
     * @return array<array-key, T>
     */
    public function __serialize(): array
    {
        return $this->data;
    }

    /**
     * Converts a serialized string representation into an instance object.
	 * 将序列化的字符串表示形式转换为实例对象
     *
     * @deprecated The Serializable interface will go away in PHP 9.
     *
     * @link http://php.net/manual/en/serializable.unserialize.php Serializable::unserialize()
     *
     * @param string $serialized A PHP serialized string to unserialize.
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function unserialize($serialized): void
    {
        /** @var array<array-key, T> $data */
        $data = unserialize($serialized, ['allowed_classes' => false]);

        $this->data = $data;
    }

    /**
     * Adds unserialized data to the object.
	 * 将未序列化的数据添加到对象
     *
     * @param array<array-key, T> $data
     */
    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Returns the number of items in this array.
	 * 返回此数组中的项数
     *
     * @link http://php.net/manual/en/countable.count.php Countable::count()
     */
    public function count(): int
    {
        return count($this->data);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function isEmpty(): bool
    {
        return count($this->data) === 0;
    }
}
