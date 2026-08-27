<?php declare(strict_types=1);

/**
 * PhpParser，节点，类似于函数
 */

namespace PhpParser\Node;

use PhpParser\Node;

interface FunctionLike extends Node {
    /**
     * Whether to return by reference
	 * 是否通过引用返回
     */
    public function returnsByRef(): bool;

    /**
     * List of parameters
	 * 参数列表
     *
     * @return Param[]
     */
    public function getParams(): array;

    /**
     * Get the declared return type or null
	 * 获取声明的返回类型或null
     *
     * @return null|Identifier|Name|ComplexType
     */
    public function getReturnType();

    /**
     * The function body
	 * 函数主体
     *
     * @return Stmt[]|null
     */
    public function getStmts(): ?array;

    /**
     * Get PHP attribute groups.
	 * 获取PHP属性组
     *
     * @return AttributeGroup[]
     */
    public function getAttrGroups(): array;
}
