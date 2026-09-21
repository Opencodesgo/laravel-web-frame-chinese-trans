<?php declare(strict_types=1);

/**
 * PhpParser，节点，插值字符串部分
 */

namespace PhpParser\Node;

use PhpParser\NodeAbstract;

class InterpolatedStringPart extends NodeAbstract {
    /** @var string String value */
    public string $value;

    /**
     * Constructs a node representing a string part of an interpolated string.
	 * 构造表示内插字符串的字符串部分的节点
     *
     * @param string $value String value
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(string $value, array $attributes = []) {
        $this->attributes = $attributes;
        $this->value = $value;
    }

    public function getSubNodeNames(): array {
        return ['value'];
    }

    public function getType(): string {
        return 'InterpolatedStringPart';
    }
}

// @deprecated compatibility alias
class_alias(InterpolatedStringPart::class, Scalar\EncapsedStringPart::class);
