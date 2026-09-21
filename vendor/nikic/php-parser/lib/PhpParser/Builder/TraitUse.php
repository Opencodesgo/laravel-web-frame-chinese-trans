<?php declare(strict_types=1);

/**
 * PhpParser，构建者，特征使用
 */

namespace PhpParser\Builder;

use PhpParser\Builder;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Stmt;

class TraitUse implements Builder {
    /** @var Node\Name[] */
    protected array $traits = [];
    /** @var Stmt\TraitUseAdaptation[] */
    protected array $adaptations = [];

    /**
     * Creates a trait use builder.
	 * 创建一个trait使用构建器
     *
     * @param Node\Name|string ...$traits Names of used traits
     */
    public function __construct(...$traits) {
        foreach ($traits as $trait) {
            $this->and($trait);
        }
    }

    /**
     * Adds used trait.
	 * 添加used trait
     *
     * @param Node\Name|string $trait Trait name
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function and($trait) {
        $this->traits[] = BuilderHelpers::normalizeName($trait);
        return $this;
    }

    /**
     * Adds trait adaptation.
	 * 增加性状适应
     *
     * @param Stmt\TraitUseAdaptation|Builder\TraitUseAdaptation $adaptation Trait adaptation
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function with($adaptation) {
        $adaptation = BuilderHelpers::normalizeNode($adaptation);

        if (!$adaptation instanceof Stmt\TraitUseAdaptation) {
            throw new \LogicException('Adaptation must have type TraitUseAdaptation');
        }

        $this->adaptations[] = $adaptation;
        return $this;
    }

    /**
     * Returns the built node.
	 * 返回构建的节点
     *
     * @return Node The built node
     */
    public function getNode(): Node {
        return new Stmt\TraitUse($this->traits, $this->adaptations);
    }
}
