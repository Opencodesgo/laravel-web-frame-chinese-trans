<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，AssignOp，Concat
 */

namespace PhpParser\Node\Expr\AssignOp;

use PhpParser\Node\Expr\AssignOp;

class Concat extends AssignOp {
    public function getType(): string {
        return 'Expr_AssignOp_Concat';
    }
}
