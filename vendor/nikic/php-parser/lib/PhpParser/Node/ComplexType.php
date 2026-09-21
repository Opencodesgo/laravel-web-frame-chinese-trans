<?php declare(strict_types=1);

/**
 * PhpParser，节点，复合类型
 */

namespace PhpParser\Node;

use PhpParser\NodeAbstract;

/**
 * This is a base class for complex types, including nullable types and union types.
 * 这是复杂类型的基类，包括可空类型和联合类型。
 *
 * It does not provide any shared behavior and exists only for type-checking purposes.
 */
abstract class ComplexType extends NodeAbstract {
}
