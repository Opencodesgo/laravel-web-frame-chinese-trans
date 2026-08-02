<?php
/**
 * Illuminate，视图，可调用的组件变量
 */

namespace Illuminate\View;

use ArrayIterator;
use Closure;
use Illuminate\Contracts\Support\DeferringDisplayableValue;
use Illuminate\Support\Enumerable;
use IteratorAggregate;

class InvokableComponentVariable implements DeferringDisplayableValue, IteratorAggregate
{
    /**
     * The callable instance to resolve the variable value.
	 * 解析变量值的可调用实例
     *
     * @var \Closure
     */
    protected $callable;

    /**
     * Create a new variable instance.
	 * 创建新的可变实例
     *
     * @param  \Closure  $callable
     * @return void
     */
    public function __construct(Closure $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Resolve the displayable value that the class is deferring.
	 * 解析类要延迟的可显示值
     *
     * @return \Illuminate\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue()
    {
        return $this->__invoke();
    }

    /**
     * Get an interator instance for the variable.
	 * 获取变量的交互器实例
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $result = $this->__invoke();

        return new ArrayIterator($result instanceof Enumerable ? $result->all() : $result);
    }

    /**
     * Dynamically proxy attribute access to the variable.
	 * 访问变量动态代理属性
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->__invoke()->{$key};
    }

    /**
     * Dynamically proxy method access to the variable.
	 * 动态代理方法访问变量
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->__invoke()->{$method}(...$parameters);
    }

    /**
     * Resolve the variable.
	 * 解析变量
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func($this->callable);
    }

    /**
     * Resolve the variable as a string.
	 * 解析变量为字符串
     *
     * @return mixed
     */
    public function __toString()
    {
        return (string) $this->__invoke();
    }
}
