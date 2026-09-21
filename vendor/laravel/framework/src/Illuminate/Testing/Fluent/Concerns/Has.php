<?php
/**
 * Illuminate, 测试, 流利的，问题，有
 */

namespace Illuminate\Testing\Fluent\Concerns;

use Closure;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert as PHPUnit;

trait Has
{
    /**
     * Assert that the prop is of the expected size.
	 * 断言道具具有预期的大小
     *
     * @param  string|int  $key
     * @param  int|null  $length
     * @return $this
     */
    public function count($key, int $length = null): self
    {
        if (is_null($length)) {
            $path = $this->dotPath();

            PHPUnit::assertCount(
                $key,
                $this->prop(),
                $path
                    ? sprintf('Property [%s] does not have the expected size.', $path)
                    : sprintf('Root level does not have the expected size.')
            );

            return $this;
        }

        PHPUnit::assertCount(
            $length,
            $this->prop($key),
            sprintf('Property [%s] does not have the expected size.', $this->dotPath($key))
        );

        return $this;
    }

    /**
     * Ensure that the given prop exists.
	 * 确保给定的道具存在
     *
     * @param  string|int  $key
     * @param  int|\Closure|null  $length
     * @param  \Closure|null  $callback
     * @return $this
     */
    public function has($key, $length = null, Closure $callback = null): self
    {
        $prop = $this->prop();

        if (is_int($key) && is_null($length)) {
            return $this->count($key);
        }

        PHPUnit::assertTrue(
            Arr::has($prop, $key),
            sprintf('Property [%s] does not exist.', $this->dotPath($key))
        );

        $this->interactsWith($key);

        if (! is_null($callback)) {
            return $this->has($key, function (self $scope) use ($length, $callback) {
                return $scope
                    ->tap(function (self $scope) use ($length) {
                        if (! is_null($length)) {
                            $scope->count($length);
                        }
                    })
                    ->first($callback)
                    ->etc();
            });
        }

        if (is_callable($length)) {
            return $this->scope($key, $length);
        }

        if (! is_null($length)) {
            return $this->count($key, $length);
        }

        return $this;
    }

    /**
     * Assert that all of the given props exist.
	 * 断言所有给定的道具都存在
     *
     * @param  array|string  $key
     * @return $this
     */
    public function hasAll($key): self
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $prop => $count) {
            if (is_int($prop)) {
                $this->has($count);
            } else {
                $this->has($prop, $count);
            }
        }

        return $this;
    }

    /**
     * Assert that at least one of the given props exists.
	 * 断言至少有一个给定的道具存在
     *
     * @param  array|string  $key
     * @return $this
     */
    public function hasAny($key): self
    {
        $keys = is_array($key) ? $key : func_get_args();

        PHPUnit::assertTrue(
            Arr::hasAny($this->prop(), $keys),
            sprintf('None of properties [%s] exist.', implode(', ', $keys))
        );

        foreach ($keys as $key) {
            $this->interactsWith($key);
        }

        return $this;
    }

    /**
     * Assert that none of the given props exist.
	 * 断言给定的道具都不存在
     *
     * @param  array|string  $key
     * @return $this
     */
    public function missingAll($key): self
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $prop) {
            $this->missing($prop);
        }

        return $this;
    }

    /**
     * Assert that the given prop does not exist.
	 * 断言给定的道具不存在
     *
     * @param  string  $key
     * @return $this
     */
    public function missing(string $key): self
    {
        PHPUnit::assertNotTrue(
            Arr::has($this->prop(), $key),
            sprintf('Property [%s] was found while it was expected to be missing.', $this->dotPath($key))
        );

        return $this;
    }

    /**
     * Compose the absolute "dot" path to the given key.
	 * 组成给定键的绝对“点”路径
     *
     * @param  string  $key
     * @return string
     */
    abstract protected function dotPath(string $key = ''): string;

    /**
     * Marks the property as interacted.
	 * 将属性标记为交互
     *
     * @param  string  $key
     * @return void
     */
    abstract protected function interactsWith(string $key): void;

    /**
     * Retrieve a prop within the current scope using "dot" notation.
	 * 使用“点”表示法检索当前作用域中的道具
     *
     * @param  string|null  $key
     * @return mixed
     */
    abstract protected function prop(string $key = null);

    /**
     * Instantiate a new "scope" at the path of the given key.
	 * 在给定键的路径上实例化一个新的"作用域"
     *
     * @param  string  $key
     * @param  \Closure  $callback
     * @return $this
     */
    abstract protected function scope(string $key, Closure $callback);

    /**
     * Disables the interaction check.
	 * 禁用交互检查
     *
     * @return $this
     */
    abstract public function etc();

    /**
     * Instantiate a new "scope" on the first element.
	 * 在第一个元素上实例化一个新的"作用域"
     *
     * @param  \Closure  $callback
     * @return $this
     */
    abstract public function first(Closure $callback);
}
