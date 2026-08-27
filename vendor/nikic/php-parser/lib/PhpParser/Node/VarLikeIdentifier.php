<?php declare(strict_types=1);

/**
 * PhpParser，节点，类Var标识符
 */

namespace PhpParser\Node;

/**
 * Represents a name that is written in source code with a leading dollar,
 * but is not a proper variable. The leading dollar is not stored as part of the name.
 * 表示在源代码中以美元开头的名称，但不是一个合适的变量。前面的美元不作为名称的一部分存储。
 *
 * Examples: Names in property declarations are formatted as variables. Names in static property
 * lookups are also formatted as variables.
 */
class VarLikeIdentifier extends Identifier {
    public function getType(): string {
        return 'VarLikeIdentifier';
    }
}
