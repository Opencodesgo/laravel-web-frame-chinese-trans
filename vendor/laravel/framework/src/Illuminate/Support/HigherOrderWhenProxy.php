<?php
/**
 * Illuminate，支持，代理时的高阶
 */

namespace Illuminate\Support;

/**
 * @mixin \Illuminate\Support\Enumerable
 */
class HigherOrderWhenProxy
{
    /**
     * The collection being operated on.
	 * 正在操作的集合
     *
     * @var \Illuminate\Support\Enumerable
     */
    protected $collection;

    /**
     * The condition for proxying.
	 * 代理的条件
     *
     * @var bool
     */
    protected $condition;

    /**
     * Create a new proxy instance.
	 * 创建一个新的代理实例
     *
     * @param  \Illuminate\Support\Enumerable  $collection
     * @param  bool  $condition
     * @return void
     */
    public function __construct(Enumerable $collection, $condition)
    {
        $this->condition = $condition;
        $this->collection = $collection;
    }

    /**
     * Proxy accessing an attribute onto the collection.
	 * 代理访问集合上的属性
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->condition
            ? $this->collection->{$key}
            : $this->collection;
    }

    /**
     * Proxy a method call onto the collection.
	 * 将方法调用代理到集合上
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->condition
            ? $this->collection->{$method}(...$parameters)
            : $this->collection;
    }
}
