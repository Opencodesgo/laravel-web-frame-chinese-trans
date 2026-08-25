<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，Cast，Array_
 */

namespace PhpParser\Node\Expr\Cast;

use PhpParser\Node\Expr\Cast;

class Array_ extends Cast {
    public function getType(): string {
        return 'Expr_Cast_Array';
    }
}
