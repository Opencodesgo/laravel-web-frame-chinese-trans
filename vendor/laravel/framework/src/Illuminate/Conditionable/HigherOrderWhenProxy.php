<?php
/**
 * Illuminate，条件?支持，代理时的高阶
 */

namespace Illuminate\Support;

class HigherOrderWhenProxy
{
    /**
     * The target being conditionally operated on.
	 * 被有条件操作的目标
     *
     * @var mixed
     */
    protected $target;

    /**
     * The condition for proxying.
	 * 代理的条件
     *
     * @var bool
     */
    protected $condition;

    /**
     * Indicates whether the proxy has a condition.
	 * 指示代理是否具有条件
     *
     * @var bool
     */
    protected $hasCondition = false;

    /**
     * Determine whether the condition should be negated.
	 * 确定是否应该否定条件
     *
     * @var bool
     */
    protected $negateConditionOnCapture;

    /**
     * Create a new proxy instance.
	 * 创建一个新的代理实例
     *
     * @param  mixed  $target
     * @return void
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Set the condition on the proxy.
	 * 在代理上设置条件
     *
     * @param  bool  $condition
     * @return $this
     */
    public function condition($condition)
    {
        [$this->condition, $this->hasCondition] = [$condition, true];

        return $this;
    }

    /**
     * Indicate that the condition should be negated.
	 * 表明条件应该被否定
     *
     * @return $this
     */
    public function negateConditionOnCapture()
    {
        $this->negateConditionOnCapture = true;

        return $this;
    }

    /**
     * Proxy accessing an attribute onto the target.
	 * 代理将属性访问到目标上
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (! $this->hasCondition) {
            $condition = $this->target->{$key};

            return $this->condition($this->negateConditionOnCapture ? ! $condition : $condition);
        }

        return $this->condition
            ? $this->target->{$key}
            : $this->target;
    }

    /**
     * Proxy a method call on the target.
	 * 在目标上代理方法调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! $this->hasCondition) {
            $condition = $this->target->{$method}(...$parameters);

            return $this->condition($this->negateConditionOnCapture ? ! $condition : $condition);
        }

        return $this->condition
            ? $this->target->{$method}(...$parameters)
            : $this->target;
    }
}
