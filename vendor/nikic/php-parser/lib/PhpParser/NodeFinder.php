<?php declare(strict_types=1);

/**
 * PhpParser，节点查找器
 */

namespace PhpParser;

use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\FirstFindingVisitor;

class NodeFinder {
    /**
     * Find all nodes satisfying a filter callback.
	 * 发现所有满足过滤器回调的节点
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param callable $filter Filter callback: function(Node $node) : bool
     *
     * @return Node[] Found nodes satisfying the filter callback
     */
    public function find($nodes, callable $filter): array {
        if ($nodes === []) {
            return [];
        }

        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $visitor = new FindingVisitor($filter);

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($nodes);

        return $visitor->getFoundNodes();
    }

    /**
     * Find all nodes that are instances of a certain class.
	 * 查找作为某个类实例的所有节点

     * @template TNode as Node
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param class-string<TNode> $class Class name
     *
     * @return TNode[] Found nodes (all instances of $class)
     */
    public function findInstanceOf($nodes, string $class): array {
        return $this->find($nodes, function ($node) use ($class) {
            return $node instanceof $class;
        });
    }

    /**
     * Find first node satisfying a filter callback.
	 * 查找满足过滤器回调的第一个节点
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param callable $filter Filter callback: function(Node $node) : bool
     *
     * @return null|Node Found node (or null if none found)
     */
    public function findFirst($nodes, callable $filter): ?Node {
        if ($nodes === []) {
            return null;
        }

        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }

        $visitor = new FirstFindingVisitor($filter);

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($nodes);

        return $visitor->getFoundNode();
    }

    /**
     * Find first node that is an instance of a certain class.
	 * 找到第一个节点，它是某个类的实例。
     *
     * @template TNode as Node
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param class-string<TNode> $class Class name
     *
     * @return null|TNode Found node, which is an instance of $class (or null if none found)
     */
    public function findFirstInstanceOf($nodes, string $class): ?Node {
        return $this->findFirst($nodes, function ($node) use ($class) {
            return $node instanceof $class;
        });
    }
}
