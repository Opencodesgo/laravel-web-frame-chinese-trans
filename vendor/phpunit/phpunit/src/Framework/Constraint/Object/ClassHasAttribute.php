<?php declare(strict_types=1);

/**
 * PHPUnit，框架，约束，类有属性
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

use function get_class;
use function is_object;
use function sprintf;
use PHPUnit\Framework\Exception;
use ReflectionClass;
use ReflectionException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4601
 */
class ClassHasAttribute extends Constraint
{
    /**
     * @var string
     */
    private $attributeName;

    public function __construct(string $attributeName)
    {
        $this->attributeName = $attributeName;
    }

    /**
     * Returns a string representation of the constraint.
	 * 返回约束的字符串表示形式
     */
    public function toString(): string
    {
        return sprintf(
            'has attribute "%s"',
            $this->attributeName,
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
            return (new ReflectionClass($other))->hasProperty($this->attributeName);
            // @codeCoverageIgnoreStart
        } catch (ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the description of the failure.
	 * 返回失败的描述。
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
	 * 大多数情况下，失败消息的开头是“Failed asserting that”。该方法应返回这句话的后半部分。
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription($other): string
    {
        return sprintf(
            '%sclass "%s" %s',
            is_object($other) ? 'object of ' : '',
            is_object($other) ? get_class($other) : $other,
            $this->toString(),
        );
    }

    protected function attributeName(): string
    {
        return $this->attributeName;
    }
}
