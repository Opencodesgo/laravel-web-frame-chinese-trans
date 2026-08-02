<?php declare(strict_types=1);
/**
 * PhpParser，节点访问器，寻找访客
 */

namespace PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * This visitor can be used to find and collect all nodes satisfying some criterion determined by
 * a filter callback.
 * 该访问器可用于查找并收集所有满足由过滤回调函数确定的条件的节点。
 */
class FindingVisitor extends NodeVisitorAbstract {
    /** @var callable Filter callback */
    protected $filterCallback;
    /** @var Node[] Found nodes */
    protected array $foundNodes;

    public function __construct(callable $filterCallback) {
        $this->filterCallback = $filterCallback;
    }

    /**
     * Get found nodes satisfying the filter callback.
	 * 获取满足过滤器回调的已找到节点。
     *
     * Nodes are returned in pre-order.
     *
     * @return Node[] Found nodes
     */
    public function getFoundNodes(): array {
        return $this->foundNodes;
    }

    public function beforeTraverse(array $nodes): ?array {
        $this->foundNodes = [];

        return null;
    }

    public function enterNode(Node $node) {
        $filterCallback = $this->filterCallback;
        if ($filterCallback($node)) {
            $this->foundNodes[] = $node;
        }

        return null;
    }
}
