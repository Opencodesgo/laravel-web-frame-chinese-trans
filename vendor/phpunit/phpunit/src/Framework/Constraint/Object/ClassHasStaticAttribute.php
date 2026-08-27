<?php declare(strict_types=1);

/**
 * PHPUnit，框架，约束，类具有静态属性
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

use function sprintf;
use PHPUnit\Framework\Exception;
use ReflectionClass;
use ReflectionException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4601
 */
final class ClassHasStaticAttribute extends ClassHasAttribute
{
    /**
     * Returns a string representation of the constraint.
	 * 返回约束的字符串表示形式
     */
    public function toString(): string
    {
        return sprintf(
            'has static attribute "%s"',
            $this->attributeName(),
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
	 * 评估参数 $other 的约束条件。如果满足约束条件则返回 true，否则返回 false。
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        try {
            $class = new ReflectionClass($other);

            if ($class->hasProperty($this->attributeName())) {
                return $class->getProperty($this->attributeName())->isStatic();
            }
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd

        return false;
    }
}
