<?php declare(strict_types=1);

/**
 * PhpParser，建立者，特质使用
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
	 * 创建一个特性使用生成器
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
	 * 增加了使用的特质
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
	 * 增加特质适应
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
	 * 返回构建节点
     *
     * @return Node The built node
     */
    public function getNode(): Node {
        return new Stmt\TraitUse($this->traits, $this->adaptations);
    }
}
