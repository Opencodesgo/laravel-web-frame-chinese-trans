<?php declare(strict_types=1);

/**
 * PhpParser，构建者，Trait
 */

namespace PhpParser\Builder;

use PhpParser;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Stmt;

class Trait_ extends Declaration {
    protected string $name;
    /** @var list<Stmt\TraitUse> */
    protected array $uses = [];
    /** @var list<Stmt\ClassConst> */
    protected array $constants = [];
    /** @var list<Stmt\Property> */
    protected array $properties = [];
    /** @var list<Stmt\ClassMethod> */
    protected array $methods = [];
    /** @var list<Node\AttributeGroup> */
    protected array $attributeGroups = [];

    /**
     * Creates an interface builder.
	 * 创建接口构建器
     *
     * @param string $name Name of the interface
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Adds a statement.
	 * 添加语句
     *
     * @param Stmt|PhpParser\Builder $stmt The statement to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmt($stmt) {
        $stmt = BuilderHelpers::normalizeNode($stmt);

        if ($stmt instanceof Stmt\Property) {
            $this->properties[] = $stmt;
        } elseif ($stmt instanceof Stmt\ClassMethod) {
            $this->methods[] = $stmt;
        } elseif ($stmt instanceof Stmt\TraitUse) {
            $this->uses[] = $stmt;
        } elseif ($stmt instanceof Stmt\ClassConst) {
            $this->constants[] = $stmt;
        } else {
            throw new \LogicException(sprintf('Unexpected node of type "%s"', $stmt->getType()));
        }

        return $this;
    }

    /**
     * Adds an attribute group.
	 * 添加属性组
     *
     * @param Node\Attribute|Node\AttributeGroup $attribute
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addAttribute($attribute) {
        $this->attributeGroups[] = BuilderHelpers::normalizeAttribute($attribute);

        return $this;
    }

    /**
     * Returns the built trait node.
	 * 返回构建的trait节点
     *
     * @return Stmt\Trait_ The built interface node
     */
    public function getNode(): PhpParser\Node {
        return new Stmt\Trait_(
            $this->name, [
                'stmts' => array_merge($this->uses, $this->constants, $this->properties, $this->methods),
                'attrGroups' => $this->attributeGroups,
            ], $this->attributes
        );
    }
}
