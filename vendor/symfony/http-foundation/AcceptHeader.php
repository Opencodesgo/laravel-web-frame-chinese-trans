<?php
/**
 * Symfony，Component，HttpFoundation，接收头
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation;

// Help opcache.preload discover always-needed symbols
class_exists(AcceptHeaderItem::class);

/**
 * Represents an Accept-* header.
 * 表示Accept-*报头。
 *
 * An accept header is compound with a list of items,
 * sorted by descending quality.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class AcceptHeader
{
    /**
     * @var AcceptHeaderItem[]
     */
    private array $items = [];

    private bool $sorted = true;

    /**
     * @param AcceptHeaderItem[] $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Builds an AcceptHeader instance from a string.
	 * 从字符串生成一个AcceptHeader实例
     */
    public static function fromString(?string $headerValue): self
    {
        $parts = HeaderUtils::split($headerValue ?? '', ',;=');

        return new self(array_map(function ($subParts) {
            static $index = 0;
            $part = array_shift($subParts);
            $attributes = HeaderUtils::combine($subParts);

            $item = new AcceptHeaderItem($part[0], $attributes);
            $item->setIndex($index++);

            return $item;
        }, $parts));
    }

    /**
     * Returns header value's string representation.
	 * 返回报头值的字符串表示形式
     */
    public function __toString(): string
    {
        return implode(',', $this->items);
    }

    /**
     * Tests if header has given value.
	 * 测试头文件是否给定值
     */
    public function has(string $value): bool
    {
        return isset($this->items[$value]);
    }

    /**
     * Returns given value's item, if exists.
	 * 返回给定值的项，如果存在。
     */
    public function get(string $value): ?AcceptHeaderItem
    {
        return $this->items[$value] ?? $this->items[explode('/', $value)[0].'/*'] ?? $this->items['*/*'] ?? $this->items['*'] ?? null;
    }

    /**
     * Adds an item.
	 * 添加项
     *
     * @return $this
     */
    public function add(AcceptHeaderItem $item): static
    {
        $this->items[$item->getValue()] = $item;
        $this->sorted = false;

        return $this;
    }

    /**
     * Returns all items.
	 * 返回所有项
     *
     * @return AcceptHeaderItem[]
     */
    public function all(): array
    {
        $this->sort();

        return $this->items;
    }

    /**
     * Filters items on their value using given regex.
	 * 使用给定的正则表达式过滤项的值
	 * 
     */
    public function filter(string $pattern): self
    {
        return new self(array_filter($this->items, fn (AcceptHeaderItem $item) => preg_match($pattern, $item->getValue())));
    }

    /**
     * Returns first item.
	 * 返回第一项
     */
    public function first(): ?AcceptHeaderItem
    {
        $this->sort();

        return $this->items ? reset($this->items) : null;
    }

    /**
     * Sorts items by descending quality.
	 * 按质量降序排序
     */
    private function sort(): void
    {
        if (!$this->sorted) {
            uasort($this->items, function (AcceptHeaderItem $a, AcceptHeaderItem $b) {
                $qA = $a->getQuality();
                $qB = $b->getQuality();

                if ($qA === $qB) {
                    return $a->getIndex() > $b->getIndex() ? 1 : -1;
                }

                return $qA > $qB ? -1 : 1;
            });

            $this->sorted = true;
        }
    }
}
