<?php
/**
 * League，CommonMark，工具，数组集合
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Util;

/**
 * Array collection
 * 数据集合
 *
 * Provides a wrapper around a standard PHP array.
 * 提供标准PHP数组的包装器。
 *
 * @internal
 *
 * @phpstan-template T
 * @phpstan-implements \IteratorAggregate<int, T>
 * @phpstan-implements \ArrayAccess<int, T>
 */
final class ArrayCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array<int, mixed>
     * @phpstan-var array<int, T>
     */
    private array $elements;

    /**
     * Constructor
	 * 构造方法
     *
     * @param array<int|string, mixed> $elements
     *
     * @phpstan-param array<int, T> $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @return mixed|false
     *
     * @phpstan-return T|false
     */
    public function first()
    {
        return \reset($this->elements);
    }

    /**
     * @return mixed|false
     *
     * @phpstan-return T|false
     */
    public function last()
    {
        return \end($this->elements);
    }

    /**
     * Retrieve an external iterator
	 * 检索外部迭代器
     *
     * @return \ArrayIterator<int, mixed>
     *
     * @phpstan-return \ArrayIterator<int, T>
     */
    #[\ReturnTypeWillChange]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * Count elements of an object
	 * 对对象的元素进行计数
     *
     * @return int The count as an integer.
     */
    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * Whether an offset exists
	 * 是否存在偏移量
     *
     * {@inheritDoc}
     *
     * @phpstan-param int $offset
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->elements);
    }

    /**
     * Offset to retrieve
	 * 检索偏移量
     *
     * {@inheritDoc}
     *
     * @phpstan-param int $offset
     *
     * @phpstan-return T|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->elements[$offset] ?? null;
    }

    /**
     * Offset to set
     *
     * {@inheritDoc}
     *
     * @phpstan-param int|null $offset
     * @phpstan-param T        $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * {@inheritDoc}
     *
     * @phpstan-param int $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        if (! \array_key_exists($offset, $this->elements)) {
            return;
        }

        unset($this->elements[$offset]);
    }

    /**
     * Returns a subset of the array
	 * 返回数组的子集
     *
     * @return array<int, mixed>
     *
     * @phpstan-return array<int, T>
     */
    public function slice(int $offset, ?int $length = null): array
    {
        return \array_slice($this->elements, $offset, $length, true);
    }

    /**
     * @return array<int, mixed>
     *
     * @phpstan-return array<int, T>
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
