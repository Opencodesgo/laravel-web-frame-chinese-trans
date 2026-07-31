<?php
/**
 * GrahamCampbell，ResultType，结果
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

/**
 * @template T
 * @template E
 */
abstract class Result
{
    /**
     * Get the success option value.
	 * 获取成功选项值
     *
     * @return \PhpOption\Option<T>
     */
    abstract public function success();

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
    abstract public function map(callable $f);

    /**
     * Flat map over the success value.
	 * 成功值的平面映射
     *
     * @template S
     * @template F
     *
     * @param callable(T):\GrahamCampbell\ResultType\Result<S,F> $f
     *
     * @return \GrahamCampbell\ResultType\Result<S,F>
     */
    abstract public function flatMap(callable $f);

    /**
     * Get the error option value.
	 * 获取错误选项值
     *
     * @return \PhpOption\Option<E>
     */
    abstract public function error();

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
    abstract public function mapError(callable $f);
}
