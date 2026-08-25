<?php declare(strict_types=1);

/**
 * PhpParser，节点，类 Var标识符
 */

namespace PhpParser\Node;

/**
 * Represents a name that is written in source code with a leading dollar,
 * but is not a proper variable. The leading dollar is not stored as part of the name.
 * 表示一个在源代码中以美元符号开头的名称，但并非有效的变量。该开头的美元符号不会被存储为名称的一部分。
 *
 * Examples: Names in property declarations are formatted as variables. Names in static property
 * lookups are also formatted as variables.
 */
class VarLikeIdentifier extends Identifier {
    public function getType(): string {
        return 'VarLikeIdentifier';
    }
}
