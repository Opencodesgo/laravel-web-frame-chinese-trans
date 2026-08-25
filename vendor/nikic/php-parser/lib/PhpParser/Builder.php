<?php declare(strict_types=1);

/**
 * PhpParser，构建者
 */

namespace PhpParser;

interface Builder {
    /**
     * Returns the built node.
	 * 返回构建的节点
     *
     * @return Node The built node
     */
    public function getNode(): Node;
}
