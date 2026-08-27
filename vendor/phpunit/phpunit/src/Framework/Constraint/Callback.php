<?php declare(strict_types=1);

/**
 * PHPUnit，框架，约束，回调
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
 * @psalm-template CallbackInput of mixed
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Callback extends Constraint
{
    /**
     * @var callable
     *
     * @psalm-var callable(CallbackInput $input): bool
     */
    private $callback;

    /** @psalm-param callable(CallbackInput $input): bool $callback */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Returns a string representation of the constraint.
	 * 返回约束的字符串表示形式
     */
    public function toString(): string
    {
        return 'is accepted by specified callback';
    }

    /**
     * Evaluates the constraint for parameter $value. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     *
     * @psalm-param CallbackInput $other
     */
    protected function matches($other): bool
    {
        return ($this->callback)($other);
    }
}
