<?php
/**
 * Psy，代码清理，命名空间感知传递
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\CodeCleaner;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Stmt\Namespace_;

/**
 * Abstract namespace-aware code cleaner pass.
 * 抽象名称空间感知代码清理器通过。
 */
abstract class NamespaceAwarePass extends CodeCleanerPass
{
    protected array $namespace = [];
    protected array $currentScope = [];

    /**
     * @todo should this be final? Extending classes should be sure to either
     * use afterTraverse or call parent::beforeTraverse() when overloading.
     *
     * Reset the namespace and the current scope before beginning analysis
     *
     * @return Node[]|null Array of nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->namespace = [];
        $this->currentScope = [];
    }

    /**
     * @todo should this be final? Extending classes should be sure to either use
     * leaveNode or call parent::enterNode() when overloading
     *
     * @param Node $node
     *
     * @return int|Node|null Replacement node (or special return value)
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = isset($node->name) ? $this->getParts($node->name) : [];
        }
    }

    /**
     * Get a fully-qualified name (class, function, interface, etc).
	 * 获取全限定名称（类、函数、接口等）
     *
     * @param mixed $name
     */
    protected function getFullyQualifiedName($name): string
    {
        if ($name instanceof FullyQualifiedName) {
            return \implode('\\', $this->getParts($name));
        }

        if ($name instanceof Name) {
            $name = $this->getParts($name);
        } elseif (!\is_array($name)) {
            $name = [$name];
        }

        return \implode('\\', \array_merge($this->namespace, $name));
    }

    /**
     * Backwards compatibility shim for PHP-Parser 4.x.
	 * PHP-Parser 4.x的向后兼容性。
     *
     * At some point we might want to make $namespace a plain string, to match how Name works?
     */
    protected function getParts(Name $name): array
    {
        return \method_exists($name, 'getParts') ? $name->getParts() : $name->parts;
    }
}
