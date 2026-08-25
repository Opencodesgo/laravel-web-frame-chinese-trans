<?php declare(strict_types=1);

/**
 * PhpParser，节点，表达式，Call Like
 */

namespace PhpParser\Node\Expr;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\VariadicPlaceholder;

abstract class CallLike extends Expr {
    /**
     * Return raw arguments, which may be actual Args, or VariadicPlaceholders for first-class
     * callables.
	 * 返回原始参数，这些参数可能是实际的 Args，也可能是用于一等可调用函数的变长参数占位符。
     *
     * @return array<Arg|VariadicPlaceholder>
     */
    abstract public function getRawArgs(): array;

    /**
     * Returns whether this call expression is actually a first class callable.
	 * 返回此调用表达式是否实际上是第一类可调用的
     */
    public function isFirstClassCallable(): bool {
        $rawArgs = $this->getRawArgs();
        return count($rawArgs) === 1 && current($rawArgs) instanceof VariadicPlaceholder;
    }

    /**
     * Assert that this is not a first-class callable and return only ordinary Args.
	 * 断言这不是一级可调用对象，并且只返回普通的Args。
     *
     * @return Arg[]
     */
    public function getArgs(): array {
        assert(!$this->isFirstClassCallable());
        return $this->getRawArgs();
    }
}
