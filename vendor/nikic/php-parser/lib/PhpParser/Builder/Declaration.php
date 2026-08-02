<?php declare(strict_types=1);

/**
 * PhpParser，建立者，声明
 */

namespace PhpParser\Builder;

use PhpParser;
use PhpParser\BuilderHelpers;

abstract class Declaration implements PhpParser\Builder {
    /** @var array<string, mixed> */
    protected array $attributes = [];

    /**
     * Adds a statement.
	 * 添加语句
     *
     * @param PhpParser\Node\Stmt|PhpParser\Builder $stmt The statement to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    abstract public function addStmt($stmt);

    /**
     * Adds multiple statements.
	 * 添加多个语句
     *
     * @param (PhpParser\Node\Stmt|PhpParser\Builder)[] $stmts The statements to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmts(array $stmts) {
        foreach ($stmts as $stmt) {
            $this->addStmt($stmt);
        }

        return $this;
    }

    /**
     * Sets doc comment for the declaration.
	 * 为声明设置文档注释
     *
     * @param PhpParser\Comment\Doc|string $docComment Doc comment to set
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setDocComment($docComment) {
        $this->attributes['comments'] = [
            BuilderHelpers::normalizeDocComment($docComment)
        ];

        return $this;
    }
}
