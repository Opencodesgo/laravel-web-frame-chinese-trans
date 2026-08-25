<?php declare(strict_types=1);

/**
 * PhpParser，节点访问器，克隆访客
 */

namespace PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Visitor cloning all nodes and linking to the original nodes using an attribute.
 * 访问者克隆所有节点并使用属性链接到原始节点。
 *
 * This visitor is required to perform format-preserving pretty prints.
 * 这个访问者需要执行保持格式的漂亮打印。
 */
class CloningVisitor extends NodeVisitorAbstract {
    public function enterNode(Node $origNode) {
        $node = clone $origNode;
        $node->setAttribute('origNode', $origNode);
        return $node;
    }
}
