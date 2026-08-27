<?php declare(strict_types=1);

/**
 * PhpParser，节点，可变占位符
 */

namespace PhpParser\Node;

use PhpParser\NodeAbstract;

/**
 * Represents the "..." in "foo(...)" of the first-class callable syntax.
 * 表示一级可调用语法“foo（…）”中的“…”。
 */
class VariadicPlaceholder extends NodeAbstract {
    /**
     * Create a variadic argument placeholder (first-class callable syntax).
	 * 创建可变参数占位符（一级可调用语法）
     *
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
    }

    public function getType(): string {
        return 'VariadicPlaceholder';
    }

    public function getSubNodeNames(): array {
        return [];
    }
}
