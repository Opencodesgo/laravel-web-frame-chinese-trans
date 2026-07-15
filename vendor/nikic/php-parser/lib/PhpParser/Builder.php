<?php declare(strict_types=1);

/**
 * PhpParser，构建器
 */

namespace PhpParser;

interface Builder
{
    /**
     * Returns the built node.
     *
     * @return Node The built node
     */
    public function getNode() : Node;
}
