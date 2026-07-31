<?php declare(strict_types=1);

/**
 * PhpParser，建立者，属性
 */

namespace PhpParser\Builder;

use PhpParser;
use PhpParser\BuilderHelpers;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\ComplexType;

class Property implements PhpParser\Builder {
    protected string $name;

    protected int $flags = 0;

    protected ?Node\Expr $default = null;
    /** @var array<string, mixed> */
    protected array $attributes = [];
    /** @var null|Identifier|Name|ComplexType */
    protected ?Node $type = null;
    /** @var list<Node\AttributeGroup> */
    protected array $attributeGroups = [];
    /** @var list<Node\PropertyHook> */
    protected array $hooks = [];

    /**
     * Creates a property builder.
	 * 创建一个属性构建器
     *
     * @param string $name Name of the property
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Makes the property public.
	 * 使属性公开
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePublic() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PUBLIC);

        return $this;
    }

    /**
     * Makes the property protected.
	 * 使属性受保护
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeProtected() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PROTECTED);

        return $this;
    }

    /**
     * Makes the property private.
	 * 使属性私有
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePrivate() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PRIVATE);

        return $this;
    }

    /**
     * Makes the property static.
	 * 使属性为静态
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeStatic() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::STATIC);

        return $this;
    }

    /**
     * Makes the property readonly.
	 * 使属性为只读
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeReadonly() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::READONLY);

        return $this;
    }

    /**
     * Makes the property abstract. Requires at least one property hook to be specified as well.
	 * 使属性抽象。还要求指定至少一个属性钩子。
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeAbstract() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::ABSTRACT);

        return $this;
    }

    /**
     * Makes the property final.
	 * 使属性为最终值
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeFinal() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::FINAL);

        return $this;
    }

    /**
     * Gives the property private(set) visibility.
	 * 赋予属性private（set）可见性
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makePrivateSet() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PRIVATE_SET);

        return $this;
    }

    /**
     * Gives the property protected(set) visibility.
	 * 赋予属性protected（set）可见性
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeProtectedSet() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Modifiers::PROTECTED_SET);

        return $this;
    }

    /**
     * Sets default value for the property.
	 * 设置属性的默认值
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
     * Sets doc comment for the property.
	 * 为属性设置文档注释
     *
     * @param PhpParser\Comment\Doc|string $docComment Doc comment to set
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setDocComment($docComment) {
        $this->attributes = [
            'comments' => [BuilderHelpers::normalizeDocComment($docComment)]
        ];

        return $this;
    }

    /**
     * Sets the property type for PHP 7.4+.
     *
     * @param string|Name|Identifier|ComplexType $type
     *
     * @return $this
     */
    public function setType($type) {
        $this->type = BuilderHelpers::normalizeType($type);

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
     * Adds a property hook.
	 * 添加属性钩子
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addHook(Node\PropertyHook $hook) {
        $this->hooks[] = $hook;

        return $this;
    }

    /**
     * Returns the built class node.
	 * 返回构建的类节点
     *
     * @return Stmt\Property The built property node
     */
    public function getNode(): PhpParser\Node {
        if ($this->flags & Modifiers::ABSTRACT && !$this->hooks) {
            throw new PhpParser\Error('Only hooked properties may be declared abstract');
        }

        return new Stmt\Property(
            $this->flags !== 0 ? $this->flags : Modifiers::PUBLIC,
            [
                new Node\PropertyItem($this->name, $this->default)
            ],
            $this->attributes,
            $this->type,
            $this->attributeGroups,
            $this->hooks
        );
    }
}
