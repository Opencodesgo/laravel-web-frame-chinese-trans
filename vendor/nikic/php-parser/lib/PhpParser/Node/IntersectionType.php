<?php declare(strict_types=1);

/**
 * PhpParser，节点，复合类型
 */

namespace PhpParser\Node;

class IntersectionType extends ComplexType {
    /** @var (Identifier|Name)[] Types */
    public array $types;

    /**
     * Constructs an intersection type.
	 * 构造一个交集类型
     *
     * @param (Identifier|Name)[] $types Types
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(array $types, array $attributes = []) {
        $this->attributes = $attributes;
        $this->types = $types;
    }

    public function getSubNodeNames(): array {
        return ['types'];
    }

    public function getType(): string {
        return 'IntersectionType';
    }
}
