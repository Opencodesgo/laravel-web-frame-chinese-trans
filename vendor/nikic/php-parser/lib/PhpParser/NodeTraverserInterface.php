<?php declare(strict_types=1);

/**
 * PhpParser，节点遍历接口
 */

namespace PhpParser;

interface NodeTraverserInterface {
    /**
     * Adds a visitor.
	 * 添加访客
     *
     * @param NodeVisitor $visitor Visitor to add
     */
    public function addVisitor(NodeVisitor $visitor): void;

    /**
     * Removes an added visitor.
	 * 删除已添加的访问者
     */
    public function removeVisitor(NodeVisitor $visitor): void;

    /**
     * Traverses an array of nodes using the registered visitors.
	 * 使用已注册的访问者遍历节点数组
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return Node[] Traversed array of nodes
     */
    public function traverse(array $nodes): array;
}
