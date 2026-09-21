<?php declare(strict_types=1);

/**
 * PHPUnit，框架，约束，Is Json
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

use function json_decode;
use function json_last_error;
use function sprintf;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsJson extends Constraint
{
    /**
     * Returns a string representation of the constraint.
	 * 返回约束的字符串表示形式
     */
    public function toString(): string
    {
        return 'is valid JSON';
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
        if ($other === '') {
            return false;
        }

        json_decode($other);

        if (json_last_error()) {
            return false;
        }

        return true;
    }

    /**
     * Returns the description of the failure.
	 * 返回失败的描述。
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        if ($other === '') {
            return 'an empty string is valid JSON';
        }

        json_decode($other);
        $error = (string) JsonMatchesErrorMessageProvider::determineJsonError(
            (string) json_last_error(),
        );

        return sprintf(
            '%s is valid JSON (%s)',
            $this->exporter()->shortenedExport($other),
            $error,
        );
    }
}
