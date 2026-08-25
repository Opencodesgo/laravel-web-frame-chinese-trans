<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，Bitwise Not
 */

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;

class BitwiseNot extends Expr {
    /** @var Expr Expression */
    public Expr $expr;

    /**
     * Constructs a bitwise not node.
	 * 构造一个非位节点
     *
     * @param Expr $expr Expression
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Expr $expr, array $attributes = []) {
        $this->attributes = $attributes;
        $this->expr = $expr;
    }

    public function getSubNodeNames(): array {
        return ['expr'];
    }

    public function getType(): string {
        return 'Expr_BitwiseNot';
    }
}
