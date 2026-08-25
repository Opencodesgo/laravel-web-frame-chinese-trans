<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，AssignOp，Bitwise Or
 */

namespace PhpParser\Node\Expr\AssignOp;

use PhpParser\Node\Expr\AssignOp;

class BitwiseOr extends AssignOp {
    public function getType(): string {
        return 'Expr_AssignOp_BitwiseOr';
    }
}
