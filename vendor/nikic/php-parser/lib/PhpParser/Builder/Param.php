<?php declare(strict_types=1);

/**
 * PhpParser，构建者，参数
 */

namespace PhpParser\Builder;

use PhpParser;
use PhpParser\BuilderHelpers;
use PhpParser\Modifiers;
use PhpParser\Node;

class Param implements PhpParser\Builder {
    protected string $name;
    protected ?Node\Expr $default = null;
    /** @var Node\Identifier|Node\Name|Node\ComplexType|null */
    protected ?Node $type = null;
    protected bool $byRef = false;
    protected int $flags = 0;
    protected bool $variadic = false;
    /** @var list<Node\AttributeGroup> */
    protected array $attributeGroups = [];

    /**
     * Creates a parameter builder.
	 * 创建参数构建器
     *
     * @param string $name Name of the parameter
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Sets default value for the parameter.
	 * 设置参数的默认值
     *
     * @param mixed $value Default value to use
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setDefault($value) {
        $this->default = BuilderHelpers::normalizeValue($value);

        return $this;
    }

    /**
     * Sets type for the parameter.
	 * 为参数设置类型
     *
     * @param string|Node\Name|Node\Identifier|Node\ComplexType $type Parameter type
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setType($type) {
        $this->type = BuilderHelpers::normalizeType($type);
        if ($this->type == 'void') {
            throw new \LogicException('Parameter type cannot be void');
        }

        return $this;
    }

    /**
     * Make the parameter accept the value by reference.
	 * 通过引用使参数接受该值
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeByRef() {
        $this->byRef = true;

        return $this;
    }

    /**
     * Make the parameter variadic
	 * 使参数可变
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeVariadic() {
        $this->variadic = true;

        return $this;
    }

    /**
     * Makes the (promoted) parameter public.
	 * 使（提升的）参数公开
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePublic() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PUBLIC);

        return $this;
    }

    /**
     * Makes the (promoted) parameter protected.
	 * 使（提升的）参数受到保护
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeProtected() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PROTECTED);

        return $this;
    }

    /**
     * Makes the (promoted) parameter private.
	 * 将（提升的）参数设为私有
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePrivate() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PRIVATE);

        return $this;
    }

    /**
     * Makes the (promoted) parameter readonly.
	 * 使（提升的）参数只读
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeReadonly() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::READONLY);

        return $this;
    }

    /**
     * Gives the promoted property private(set) visibility.
	 * 提供提升属性private（set）可见性
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePrivateSet() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PRIVATE_SET);

        return $this;
    }

    /**
     * Gives the promoted property protected(set) visibility.
	 * 提供提升的属性protected（set）可见性
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeProtectedSet() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PROTECTED_SET);

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
     * Returns the built parameter node.
	 * 返回生成的参数节点
     *
     * @return Node\Param The built parameter node
     */
    public function getNode(): Node {
        return new Node\Param(
            new Node\Expr\Variable($this->name),
            $this->default, $this->type, $this->byRef, $this->variadic, [], $this->flags, $this->attributeGroups
        );
    }
}
