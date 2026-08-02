<?php
/**
 * GrahamCampbell，ResultType，错误
 */

declare(strict_types=1);

/*
 * This file is part of Result Type.
 *
 * (c) Graham Campbell <hello@gjcampbell.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\ResultType;

use PhpOption\None;
use PhpOption\Some;

/**
 * @template T
 * @template E
 *
 * @extends \GrahamCampbell\ResultType\Result<T,E>
 */
final class Error extends Result
{
    /**
     * @var E
     */
    private $value;

    /**
     * Internal constructor for an error value.
	 * 错误值的内部构造函数
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
     * @return \GrahamCampbell\ResultType\Result<T,F>
     */
    public static function create($value)
    {
        return new self($value);
    }

    /**
     * Get the success option value.
	 * 获取成功选项值
     *
     * @return \PhpOption\Option<T>
     */
    public function success()
    {
        return None::create();
    }

    /**
     * Map over the success value.
	 * 映射成功值
     *
     * @template S
     *
     * @param callable(T):S $f
     *
     * @return \GrahamCampbell\ResultType\Result<S,E>
     */
    public function map(callable $f)
    {
        return self::create($this->value);
    }

    /**
     * Flat map over the success value.
	 * 平面地图超过成功值
     *
     * @template S
     * @template F
     *
     * @param callable(T):\GrahamCampbell\ResultType\Result<S,F> $f
     *
     * @return \GrahamCampbell\ResultType\Result<S,F>
     */
    public function flatMap(callable $f)
    {
        /** @var \GrahamCampbell\ResultType\Result<S,F> */
        return self::create($this->value);
    }

    /**
     * Get the error option value.
	 * 获取错误选项值
     *
     * @return \PhpOption\Option<E>
     */
    public function error()
    {
        return Some::create($this->value);
    }

    /**
     * Map over the error value.
	 * 映射到错误值
     *
     * @template F
     *
     * @param callable(E):F $f
     *
     * @return \GrahamCampbell\ResultType\Result<T,F>
     */
    public function mapError(callable $f)
    {
        return self::create($f($this->value));
    }
}
