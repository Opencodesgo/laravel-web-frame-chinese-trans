<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，二进制 Op，位异或
 */

namespace PhpParser\Node\Expr\BinaryOp;

use PhpParser\Node\Expr\BinaryOp;

class BitwiseOr extends BinaryOp {
    public function getOperatorSigil(): string {
        return '|';
    }

    public function getType(): string {
        return 'Expr_BinaryOp_BitwiseOr';
    }
}
