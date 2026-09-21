<?php
/**
 * Symfony，Component，HttpFoundation，接收报头项
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

/**
 * Represents an Accept-* header item.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class AcceptHeaderItem
{
    private string $value;
    private float $quality = 1.0;
    private int $index = 0;
    private array $attributes = [];

    public function __construct(string $value, array $attributes = [])
    {
        $this->value = $value;
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Builds an AcceptHeaderInstance instance from a string.
	 * 从字符串构建一个AcceptHeaderInstance实例
     */
    public static function fromString(?string $itemValue): self
    {
        $parts = HeaderUtils::split($itemValue ?? '', ';=');

        $part = array_shift($parts);
        $attributes = HeaderUtils::combine($parts);

        return new self($part[0], $attributes);
    }

    /**
     * Returns header value's string representation.
	 * 返回报头值的字符串表示形式
     */
    public function __toString(): string
    {
        $string = $this->value.($this->quality < 1 ? ';q='.$this->quality : '');
        if (\count($this->attributes) > 0) {
            $string .= '; '.HeaderUtils::toString($this->attributes, ';');
        }

        return $string;
    }

    /**
     * Set the item value.
	 * 设置项目值
     *
     * @return $this
     */
    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns the item value.
	 * 返回项值
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set the item quality.
	 * 设置项目质量
     *
     * @return $this
     */
    public function setQuality(float $quality): static
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Returns the item quality.
	 * 返回项目质量
     */
    public function getQuality(): float
    {
        return $this->quality;
    }

    /**
     * Set the item index.
	 * 设置项目索引
     *
     * @return $this
     */
    public function setIndex(int $index): static
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Returns the item index.
	 * 返回项目索引
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Tests if an attribute exists.
	 * 测试属性是否存在
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Returns an attribute by its name.
	 * 按名称返回属性
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * Returns all attributes.
	 * 返回所有属性
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set an attribute.
	 * 设置属性
     *
     * @return $this
     */
    public function setAttribute(string $name, string $value): static
    {
        if ('q' === $name) {
            $this->quality = (float) $value;
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }
}
