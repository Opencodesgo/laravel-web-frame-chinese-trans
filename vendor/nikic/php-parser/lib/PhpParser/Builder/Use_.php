<?php declare(strict_types=1);

/**
 * PhpParser，建立者，Use_
 */

namespace PhpParser\Builder;

use PhpParser\Builder;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Stmt;

class Use_ implements Builder {
    protected Node\Name $name;
    /** @var Stmt\Use_::TYPE_* */
    protected int $type;
    protected ?string $alias = null;

    /**
     * Creates a name use (alias) builder.
	 * 创建名称使用(别名)生成器
     *
     * @param Node\Name|string $name Name of the entity (namespace, class, function, constant) to alias
     * @param Stmt\Use_::TYPE_* $type One of the Stmt\Use_::TYPE_* constants
     */
    public function __construct($name, int $type) {
        $this->name = BuilderHelpers::normalizeName($name);
        $this->type = $type;
    }

    /**
     * Sets alias for used name.
	 * 为使用的名称设置别名
     *
     * @param string $alias Alias to use (last component of full name by default)
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function as(string $alias) {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Returns the built node.
	 * 返回构建的节点
     *
     * @return Stmt\Use_ The built node
     */
    public function getNode(): Node {
        return new Stmt\Use_([
            new Node\UseItem($this->name, $this->alias)
        ], $this->type);
    }
}
