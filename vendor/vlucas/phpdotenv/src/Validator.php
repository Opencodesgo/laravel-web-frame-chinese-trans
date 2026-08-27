<?php
/**
 * Dotenv，验证器
 */

declare(strict_types=1);

namespace Dotenv;

use Dotenv\Exception\ValidationException;
use Dotenv\Repository\RepositoryInterface;
use Dotenv\Util\Regex;
use Dotenv\Util\Str;

class Validator
{
    /**
     * The environment repository instance.
	 * 环境存储库实例
     *
     * @var \Dotenv\Repository\RepositoryInterface
     */
    private $repository;

    /**
     * The variables to validate.
	 * 要验证的变量
     *
     * @var string[]
     */
    private $variables;

    /**
     * Create a new validator instance.
	 * 创建一个新的验证器实例
     *
     * @param \Dotenv\Repository\RepositoryInterface $repository
     * @param string[]                               $variables
     *
     * @return void
     */
    public function __construct(RepositoryInterface $repository, array $variables)
    {
        $this->repository = $repository;
        $this->variables = $variables;
    }

    /**
     * Assert that each variable is present.
	 * 断言每个变量都存在
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function required()
    {
        return $this->assert(
            static function (?string $value) {
                return $value !== null;
            },
            'is missing'
        );
    }

    /**
     * Assert that each variable is not empty.
	 * 断言每个变量不为空
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function notEmpty()
    {
        return $this->assertNullable(
            static function (string $value) {
                return Str::len(\trim($value)) > 0;
            },
            'is empty'
        );
    }

    /**
     * Assert that each specified variable is an integer.
	 * 断言每个指定的变量都是整数
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function isInteger()
    {
        return $this->assertNullable(
            static function (string $value) {
                return \ctype_digit($value);
            },
            'is not an integer'
        );
    }

    /**
     * Assert that each specified variable is a boolean.
	 * 断言每个指定的变量都是布尔值
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function isBoolean()
    {
        return $this->assertNullable(
            static function (string $value) {
                if ($value === '') {
                    return false;
                }

                return \filter_var($value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE) !== null;
            },
            'is not a boolean'
        );
    }

    /**
     * Assert that each variable is amongst the given choices.
	 * 断言每个变量都在给定的选项中
     *
     * @param string[] $choices
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function allowedValues(array $choices)
    {
        return $this->assertNullable(
            static function (string $value) use ($choices) {
                return \in_array($value, $choices, true);
            },
            \sprintf('is not one of [%s]', \implode(', ', $choices))
        );
    }

    /**
     * Assert that each variable matches the given regular expression.
	 * 断言每个变量都匹配给定的正则表达式
     *
     * @param string $regex
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function allowedRegexValues(string $regex)
    {
        return $this->assertNullable(
            static function (string $value) use ($regex) {
                return Regex::matches($regex, $value)->success()->getOrElse(false);
            },
            \sprintf('does not match "%s"', $regex)
        );
    }

    /**
     * Assert that the callback returns true for each variable.
	 * 断言回调函数对每个变量返回true
     *
     * @param callable(?string):bool $callback
     * @param string                 $message
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function assert(callable $callback, string $message)
    {
        $failing = [];

        foreach ($this->variables as $variable) {
            if ($callback($this->repository->get($variable)) === false) {
                $failing[] = \sprintf('%s %s', $variable, $message);
            }
        }

        if (\count($failing) > 0) {
            throw new ValidationException(\sprintf(
                'One or more environment variables failed assertions: %s.',
                \implode(', ', $failing)
            ));
        }

        return $this;
    }

    /**
     * Assert that the callback returns true for each variable.
	 * 断言回调函数对每个变量返回true
     *
     * Skip checking null variable values.
     *
     * @param callable(string):bool $callback
     * @param string                $message
     *
     * @throws \Dotenv\Exception\ValidationException
     *
     * @return \Dotenv\Validator
     */
    public function assertNullable(callable $callback, string $message)
    {
        return $this->assert(
            static function (?string $value) use ($callback) {
                if ($value === null) {
                    return true;
                }

                return $callback($value);
            },
            $message
        );
    }
}
