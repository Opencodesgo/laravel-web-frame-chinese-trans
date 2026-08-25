<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，Binary Op
 */

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;

abstract class BinaryOp extends Expr {
    /** @var Expr The left hand side expression */
    public Expr $left;
    /** @var Expr The right hand side expression */
    public Expr $right;

    /**
     * Constructs a binary operator node.
	 * 构造二进制运算符节点
     *
     * @param Expr $left The left hand side expression
     * @param Expr $right The right hand side expression
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Expr $left, Expr $right, array $attributes = []) {
        $this->attributes = $attributes;
        $this->left = $left;
        $this->right = $right;
    }

    public function getSubNodeNames(): array {
        return ['left', 'right'];
    }

    /**
     * Get the operator sigil for this binary operation.
	 * 获取此二进制操作的操作符符号。
     *
     * In the case there are multiple possible sigils for an operator, this method does not
     * necessarily return the one used in the parsed code.
	 * 如果某个操作符有多个可能的符文，此方法不一定返回解析代码中使用的那个。
     */
    abstract public function getOperatorSigil(): string;
}
