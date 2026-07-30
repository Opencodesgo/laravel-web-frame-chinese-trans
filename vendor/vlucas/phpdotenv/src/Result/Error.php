<?php
/**
 * Dotenv，结果，错误
 */

namespace Dotenv\Result;

use PhpOption\None;
use PhpOption\Some;

/**
 * @template T
 * @template E
 * @extends \Dotenv\Result\Result<T,E>
 */
class Error extends Result
{
    /**
     * @var E
     */
    private $value;

    /**
     * Internal constructor for an error value.
	 * 一个错误值的内部构造函数
     *
     * @param E $value
     *
     * @return void
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Create a new error value.
	 * 创建一个新的错误值
     *
     * @template F
     *
     * @param F $value
     *
     * @return \Dotenv\Result\Result<T,F>
     */
    public static function create($value)
    {
        return new self($value);
    }

    /**
     * Get the success option value.
	 * 获得成功的期权价值
     *
     * @return \PhpOption\Option<T>
     */
    public function success()
    {
        return None::create();
    }

    /**
     * Map over the success value.
	 * 映射到成功值
     *
     * @template S
     *
     * @param callable(T):S $f
     *
     * @return \Dotenv\Result\Result<S,E>
     */
    public function mapSuccess(callable $f)
    {
        /** @var \Dotenv\Result\Result<S,E> */
        return self::create($this->value);
    }

    /**
     * Get the error option value.
     *
     * @return \PhpOption\Option<E>
     */
    public function error()
    {
        return Some::create($this->value);
    }

    /**
     * Map over the error value.
     *
     * @template F
     *
     * @param callable(E):F $f
     *
     * @return \Dotenv\Result\Result<T,F>
     */
    public function mapError(callable $f)
    {
        return self::create($f($this->value));
    }
}
