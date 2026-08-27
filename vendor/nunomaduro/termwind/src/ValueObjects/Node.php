<?php
/**
 * Termwind，值对象，节点
 */

declare(strict_types=1);

namespace Termwind\ValueObjects;

use Generator;

/**
 * @internal
 */
final class Node
{
    /**
     * A value object with helper methods for working with DOM node.
	 * 具有用于处理DOM节点的辅助方法的值对象
     */
    public function __construct(private \DOMNode $node) {}

    /**
     * Gets the value of the node.
	 * 获取节点的值
     */
    public function getValue(): string
    {
        return $this->node->nodeValue ?? '';
    }

    /**
     * Gets child nodes of the node.
	 * 获取节点的子节点
     *
     * @return Generator<Node>
     */
    public function getChildNodes(): Generator
    {
        foreach ($this->node->childNodes as $node) {
            yield new self($node);
        }
    }

    /**
     * Checks if the node is a text.
	 * 检查节点是否为文本
     */
    public function isText(): bool
    {
        return $this->node instanceof \DOMText;
    }

    /**
     * Checks if the node is a comment.
	 * 检查节点是否为注释
     */
    public function isComment(): bool
    {
        return $this->node instanceof \DOMComment;
    }

    /**
     * Compares the current node name with a given name.
	 * 将当前节点名称与给定名称进行比较
     */
    public function isName(string $name): bool
    {
        return $this->getName() === $name;
    }

    /**
     * Returns the current node type name.
	 * 返回当前节点类型名称
     */
    public function getName(): string
    {
        return $this->node->nodeName;
    }

    /**
     * Returns value of [class] attribute.
	 * 返回[class]属性的值
     */
    public function getClassAttribute(): string
    {
        return $this->getAttribute('class');
    }

    /**
     * Returns value of attribute with a given name.
	 * 返回具有给定名称的属性值
     */
    public function getAttribute(string $name): string
    {
        if ($this->node instanceof \DOMElement) {
            return $this->node->getAttribute($name);
        }

        return '';
    }

    /**
     * Checks if the node is empty.
	 * 检查节点是否为空
     */
    public function isEmpty(): bool
    {
        return $this->isText() && preg_replace('/\s+/', '', $this->getValue()) === '';
    }

    /**
     * Gets the previous sibling from the node.
	 * 从节点获取前一个兄弟节点。
     */
    public function getPreviousSibling(): static|null
    {
        $node = $this->node;

        while ($node = $node->previousSibling) {
            $node = new self($node);

            if ($node->isEmpty()) {
                $node = $node->node;

                continue;
            }

            if (! $node->isComment()) {
                return $node;
            }

            $node = $node->node;
        }

        return is_null($node) ? null : new self($node);
    }

    /**
     * Gets the next sibling from the node.
	 * 从节点获取下一个兄弟节点
     */
    public function getNextSibling(): static|null
    {
        $node = $this->node;

        while ($node = $node->nextSibling) {
            $node = new self($node);

            if ($node->isEmpty()) {
                $node = $node->node;

                continue;
            }

            if (! $node->isComment()) {
                return $node;
            }

            $node = $node->node;
        }

        return is_null($node) ? null : new self($node);
    }

    /**
     * Checks if the node is the first child.
	 * 检查节点是否是第一个子节点
     */
    public function isFirstChild(): bool
    {
        return is_null($this->getPreviousSibling());
    }

    /**
     * Gets the inner HTML representation of the node including child nodes.
	 * 获取节点（包括子节点）的内部HTML表示形式
     */
    public function getHtml(): string
    {
        $html = '';
        foreach ($this->node->childNodes as $child) {
            if ($child->ownerDocument instanceof \DOMDocument) {
                $html .= $child->ownerDocument->saveXML($child);
            }
        }

        return html_entity_decode($html);
    }

    /**
     * Converts the node to a string.
	 * 将节点转换为字符串
     */
    public function __toString(): string
    {
        if ($this->isComment()) {
            return '';
        }

        if ($this->getValue() === ' ') {
            return ' ';
        }

        if ($this->isEmpty()) {
            return '';
        }

        $text = preg_replace('/\s+/', ' ', $this->getValue()) ?? '';

        if (is_null($this->getPreviousSibling())) {
            $text = ltrim($text);
        }

        if (is_null($this->getNextSibling())) {
            $text = rtrim($text);
        }

        return $text;
    }
}
