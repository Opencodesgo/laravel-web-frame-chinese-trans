<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，分配 Op，位与
 */

namespace PhpParser\Node\Expr\AssignOp;

use PhpParser\Node\Expr\AssignOp;

class BitwiseAnd extends AssignOp {
    public function getType(): string {
        return 'Expr_AssignOp_BitwiseAnd';
    }
}
