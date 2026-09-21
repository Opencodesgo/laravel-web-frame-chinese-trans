<?php declare(strict_types=1);

/**
 * PHPUnit，框架，约束，逻辑或
 */

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class LogicalOr extends BinaryOperator
{
    /**
     * Returns the name of this operator.
	 * 返回此操作符的名称
     */
    public function operator(): string
    {
        return 'or';
    }

    /**
     * Returns this operator's precedence.
	 * 返回此操作符的优先级
     *
     * @see https://www.php.net/manual/en/language.operators.precedence.php
     */
    public function precedence(): int
    {
        return 24;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
	 * 评估参数 $other 的约束条件。如果满足约束条件则返回 true，否则返回 false。
     *
     * @param mixed $other value or object to evaluate
     */
    public function matches($other): bool
    {
        foreach ($this->constraints() as $constraint) {
            if ($constraint->evaluate($other, '', true)) {
                return true;
            }
        }

        return false;
    }
}
