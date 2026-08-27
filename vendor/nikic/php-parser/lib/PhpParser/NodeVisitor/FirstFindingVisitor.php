<?php declare(strict_types=1);

/**
 * PhpParser，节点访问器，第一个发现访客
 */

namespace PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * This visitor can be used to find the first node satisfying some criterion determined by
 * a filter callback.
 * 此访问器可用于查找满足由确定的某些标准的第一个节点。
 */
class FirstFindingVisitor extends NodeVisitorAbstract {
    /** @var callable Filter callback */
    protected $filterCallback;
    /** @var null|Node Found node */
    protected ?Node $foundNode;

    public function __construct(callable $filterCallback) {
        $this->filterCallback = $filterCallback;
    }

    /**
     * Get found node satisfying the filter callback.
	 * 获取满足过滤器回调的找到节点
     *
     * Returns null if no node satisfies the filter callback.
     *
     * @return null|Node Found node (or null if not found)
     */
    public function getFoundNode(): ?Node {
        return $this->foundNode;
    }

    public function beforeTraverse(array $nodes): ?array {
        $this->foundNode = null;

        return null;
    }

    public function enterNode(Node $node) {
        $filterCallback = $this->filterCallback;
        if ($filterCallback($node)) {
            $this->foundNode = $node;
            return NodeVisitor::STOP_TRAVERSAL;
        }

        return null;
    }
}
