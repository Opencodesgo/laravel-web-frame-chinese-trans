<?php
/**
 * Dotenv，结果，Result
 */

namespace Dotenv\Result;

/**
 * @template T
 * @template E
 */
abstract class Result
{
    /**
     * Get the success option value.
	 * 获得成功的期权价值
     *
     * @return \PhpOption\Option<T>
     */
    abstract public function success();

    /**
     * Get the success value, if possible.
	 * 如果可能的话,获得成功的价值。
     *
     * @throws \RuntimeException
     *
     * @return T
     */
    public function getSuccess()
    {
        return $this->success()->get();
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
    abstract public function mapSuccess(callable $f);

    /**
     * Get the error option value.
	 * 获取错误选项值
     *
     * @return \PhpOption\Option<E>
     */
    abstract public function error();

    /**
     * Get the error value, if possible.
     *
     * @throws \RuntimeException
     *
     * @return E
     */
    public function getError()
    {
        return $this->error()->get();
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
    abstract public function mapError(callable $f);
}
