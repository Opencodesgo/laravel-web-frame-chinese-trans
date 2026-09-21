<?php declare(strict_types=1);

/**
 * PhpParser，建立者工厂
 */

namespace PhpParser;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Use_;

class BuilderFactory {
    /**
     * Creates an attribute node.
	 * 创建属性节点
     *
     * @param string|Name $name Name of the attribute
     * @param array $args Attribute named arguments
     */
    public function attribute($name, array $args = []): Node\Attribute {
        return new Node\Attribute(
            BuilderHelpers::normalizeName($name),
            $this->args($args)
        );
    }

    /**
     * Creates a namespace builder.
	 * 创建名称空间构建器
     *
     * @param null|string|Node\Name $name Name of the namespace
     *
     * @return Builder\Namespace_ The created namespace builder
     */
    public function namespace($name): Builder\Namespace_ {
        return new Builder\Namespace_($name);
    }

    /**
     * Creates a class builder.
	 * 创建一个类构建器。
     *
     * @param string $name Name of the class
     *
     * @return Builder\Class_ The created class builder
     */
    public function class(string $name): Builder\Class_ {
        return new Builder\Class_($name);
    }

    /**
     * Creates an interface builder.
	 * 创建接口构建器。
     *
     * @param string $name Name of the interface
     *
     * @return Builder\Interface_ The created interface builder
     */
    public function interface(string $name): Builder\Interface_ {
        return new Builder\Interface_($name);
    }

    /**
     * Creates a trait builder.
	 * 创建一个特质构建器
     *
     * @param string $name Name of the trait
     *
     * @return Builder\Trait_ The created trait builder
     */
    public function trait(string $name): Builder\Trait_ {
        return new Builder\Trait_($name);
    }

    /**
     * Creates an enum builder.
	 * 创建枚举生成器
     *
     * @param string $name Name of the enum
     *
     * @return Builder\Enum_ The created enum builder
     */
    public function enum(string $name): Builder\Enum_ {
        return new Builder\Enum_($name);
    }

    /**
     * Creates a trait use builder.
	 * 创建一个trait使用构建器
     *
     * @param Node\Name|string ...$traits Trait names
     *
     * @return Builder\TraitUse The created trait use builder
     */
    public function useTrait(...$traits): Builder\TraitUse {
        return new Builder\TraitUse(...$traits);
    }

    /**
     * Creates a trait use adaptation builder.
	 * 创建一个特征使用适应构建器
     *
     * @param Node\Name|string|null $trait Trait name
     * @param Node\Identifier|string $method Method name
     *
     * @return Builder\TraitUseAdaptation The created trait use adaptation builder
     */
    public function traitUseAdaptation($trait, $method = null): Builder\TraitUseAdaptation {
        if ($method === null) {
            $method = $trait;
            $trait = null;
        }

        return new Builder\TraitUseAdaptation($trait, $method);
    }

    /**
     * Creates a method builder.
	 * 创建一个方法构建器
     *
     * @param string $name Name of the method
     *
     * @return Builder\Method The created method builder
     */
    public function method(string $name): Builder\Method {
        return new Builder\Method($name);
    }

    /**
     * Creates a parameter builder.
	 * 创建参数构建器
     *
     * @param string $name Name of the parameter
     *
     * @return Builder\Param The created parameter builder
     */
    public function param(string $name): Builder\Param {
        return new Builder\Param($name);
    }

    /**
     * Creates a property builder.
	 * 创建属性构建器
     *
     * @param string $name Name of the property
     *
     * @return Builder\Property The created property builder
     */
    public function property(string $name): Builder\Property {
        return new Builder\Property($name);
    }

    /**
     * Creates a function builder.
	 * 创建一个函数构建器
     *
     * @param string $name Name of the function
     *
     * @return Builder\Function_ The created function builder
     */
    public function function(string $name): Builder\Function_ {
        return new Builder\Function_($name);
    }

    /**
     * Creates a namespace/class use builder.
	 * 创建名称空间/类使用构建器
     *
     * @param Node\Name|string $name Name of the entity (namespace or class) to alias
     *
     * @return Builder\Use_ The created use builder
     */
    public function use($name): Builder\Use_ {
        return new Builder\Use_($name, Use_::TYPE_NORMAL);
    }

    /**
     * Creates a function use builder.
	 * 创建一个函数使用生成器
     *
     * @param Node\Name|string $name Name of the function to alias
     *
     * @return Builder\Use_ The created use function builder
     */
    public function useFunction($name): Builder\Use_ {
        return new Builder\Use_($name, Use_::TYPE_FUNCTION);
    }

    /**
     * Creates a constant use builder.
	 * 创建一个常量使用构建器
     *
     * @param Node\Name|string $name Name of the const to alias
     *
     * @return Builder\Use_ The created use const builder
     */
    public function useConst($name): Builder\Use_ {
        return new Builder\Use_($name, Use_::TYPE_CONSTANT);
    }

    /**
     * Creates a class constant builder.
	 * 创建类常量构建器
     *
     * @param string|Identifier $name Name
     * @param Node\Expr|bool|null|int|float|string|array $value Value
     *
     * @return Builder\ClassConst The created use const builder
     */
    public function classConst($name, $value): Builder\ClassConst {
        return new Builder\ClassConst($name, $value);
    }

    /**
     * Creates an enum case builder.
	 * 创建枚举案例构建器
     *
     * @param string|Identifier $name Name
     *
     * @return Builder\EnumCase The created use const builder
     */
    public function enumCase($name): Builder\EnumCase {
        return new Builder\EnumCase($name);
    }

    /**
     * Creates node a for a literal value.
	 * 为文字值创建节点a
     *
     * @param Expr|bool|null|int|float|string|array|\UnitEnum $value $value
     */
    public function val($value): Expr {
        return BuilderHelpers::normalizeValue($value);
    }

    /**
     * Creates variable node.
	 * 创建可变节点
     *
     * @param string|Expr $name Name
     */
    public function var($name): Expr\Variable {
        if (!\is_string($name) && !$name instanceof Expr) {
            throw new \LogicException('Variable name must be string or Expr');
        }

        return new Expr\Variable($name);
    }

    /**
     * Normalizes an argument list.
	 * 规范化参数列表
     *
     * Creates Arg nodes for all arguments and converts literal values to expressions.
     *
     * @param array $args List of arguments to normalize
     *
     * @return list<Arg>
     */
    public function args(array $args): array {
        $normalizedArgs = [];
        foreach ($args as $key => $arg) {
            if (!($arg instanceof Arg)) {
                $arg = new Arg(BuilderHelpers::normalizeValue($arg));
            }
            if (\is_string($key)) {
                $arg->name = BuilderHelpers::normalizeIdentifier($key);
            }
            $normalizedArgs[] = $arg;
        }
        return $normalizedArgs;
    }

    /**
     * Creates a function call node.
	 * 创建一个函数调用节点
     *
     * @param string|Name|Expr $name Function name
     * @param array $args Function arguments
     */
    public function funcCall($name, array $args = []): Expr\FuncCall {
        return new Expr\FuncCall(
            BuilderHelpers::normalizeNameOrExpr($name),
            $this->args($args)
        );
    }

    /**
     * Creates a method call node.
	 * 创建方法调用节点
     *
     * @param Expr $var Variable the method is called on
     * @param string|Identifier|Expr $name Method name
     * @param array $args Method arguments
     */
    public function methodCall(Expr $var, $name, array $args = []): Expr\MethodCall {
        return new Expr\MethodCall(
            $var,
            BuilderHelpers::normalizeIdentifierOrExpr($name),
            $this->args($args)
        );
    }

    /**
     * Creates a static method call node.
	 * 创建静态方法调用节点
     *
     * @param string|Name|Expr $class Class name
     * @param string|Identifier|Expr $name Method name
     * @param array $args Method arguments
     */
    public function staticCall($class, $name, array $args = []): Expr\StaticCall {
        return new Expr\StaticCall(
            BuilderHelpers::normalizeNameOrExpr($class),
            BuilderHelpers::normalizeIdentifierOrExpr($name),
            $this->args($args)
        );
    }

    /**
     * Creates an object creation node.
	 * 创建对象创建节点
     *
     * @param string|Name|Expr $class Class name
     * @param array $args Constructor arguments
     */
    public function new($class, array $args = []): Expr\New_ {
        return new Expr\New_(
            BuilderHelpers::normalizeNameOrExpr($class),
            $this->args($args)
        );
    }

    /**
     * Creates a constant fetch node.
	 * 创建一个常量获取节点
     *
     * @param string|Name $name Constant name
     */
    public function constFetch($name): Expr\ConstFetch {
        return new Expr\ConstFetch(BuilderHelpers::normalizeName($name));
    }

    /**
     * Creates a property fetch node.
	 * 创建一个属性获取节点
     *
     * @param Expr $var Variable holding object
     * @param string|Identifier|Expr $name Property name
     */
    public function propertyFetch(Expr $var, $name): Expr\PropertyFetch {
        return new Expr\PropertyFetch($var, BuilderHelpers::normalizeIdentifierOrExpr($name));
    }

    /**
     * Creates a class constant fetch node.
	 * 创建类常量获取节点
     *
     * @param string|Name|Expr $class Class name
     * @param string|Identifier|Expr $name Constant name
     */
    public function classConstFetch($class, $name): Expr\ClassConstFetch {
        return new Expr\ClassConstFetch(
            BuilderHelpers::normalizeNameOrExpr($class),
            BuilderHelpers::normalizeIdentifierOrExpr($name)
        );
    }

    /**
     * Creates nested Concat nodes from a list of expressions.
	 * 从表达式列表创建嵌套的Concat节点
     *
     * @param Expr|string ...$exprs Expressions or literal strings
     */
    public function concat(...$exprs): Concat {
        $numExprs = count($exprs);
        if ($numExprs < 2) {
            throw new \LogicException('Expected at least two expressions');
        }

        $lastConcat = $this->normalizeStringExpr($exprs[0]);
        for ($i = 1; $i < $numExprs; $i++) {
            $lastConcat = new Concat($lastConcat, $this->normalizeStringExpr($exprs[$i]));
        }
        return $lastConcat;
    }

    /**
     * @param string|Expr $expr
     */
    private function normalizeStringExpr($expr): Expr {
        if ($expr instanceof Expr) {
            return $expr;
        }

        if (\is_string($expr)) {
            return new String_($expr);
        }

        throw new \LogicException('Expected string or Expr');
    }
}
