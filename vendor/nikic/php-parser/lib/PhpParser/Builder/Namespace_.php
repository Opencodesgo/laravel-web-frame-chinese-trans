<?php declare(strict_types=1);

/**
 * PhpParser，建立者，Namespace_
 */

namespace PhpParser\Builder;

use PhpParser;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Stmt;

class Namespace_ extends Declaration {
    private ?Node\Name $name;
    /** @var Stmt[] */
    private array $stmts = [];

    /**
     * Creates a namespace builder.
	 * 创建命名空间构建器
     *
     * @param Node\Name|string|null $name Name of the namespace
     */
    public function __construct($name) {
        $this->name = null !== $name ? BuilderHelpers::normalizeName($name) : null;
    }

    /**
     * Adds a statement.
	 * 添加语句
     *
     * @param Node|PhpParser\Builder $stmt The statement to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmt($stmt) {
        $this->stmts[] = BuilderHelpers::normalizeStmt($stmt);

        return $this;
    }

    /**
     * Returns the built node.
	 * 返回构建的节点
     *
     * @return Stmt\Namespace_ The built node
     */
    public function getNode(): Node {
        return new Stmt\Namespace_($this->name, $this->stmts, $this->attributes);
    }
}
