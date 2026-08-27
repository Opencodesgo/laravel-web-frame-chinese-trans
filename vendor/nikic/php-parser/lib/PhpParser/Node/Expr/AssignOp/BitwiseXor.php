<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，分配 Op，位异或
 */

namespace PhpParser\Node\Expr\AssignOp;

use PhpParser\Node\Expr\AssignOp;

class BitwiseXor extends AssignOp {
    public function getType(): string {
        return 'Expr_AssignOp_BitwiseXor';
    }
}
