<?php
/**
 * Illuminate，验证，条件规则
 */

namespace Illuminate\Validation;

use Illuminate\Support\Fluent;

class ConditionalRules
{
    /**
     * The boolean condition indicating if the rules should be added to the attribute.
	 * 指示是否应该将规则添加到属性的布尔条件
     *
     * @var callable|bool
     */
    protected $condition;

    /**
     * The rules to be added to the attribute.
	 * 要添加到属性中的规则
     *
     * @var array|string
     */
    protected $rules;

    /**
     * The rules to be added to the attribute if the condition fails.
	 * 如果条件失败，要添加到属性的规则。
     *
     * @var array|string
     */
    protected $defaultRules;

    /**
     * Create a new conditional rules instance.
	 * 创建一个新的条件规则实例
     *
     * @param  callable|bool  $condition
     * @param  array|string  $rules
     * @param  array|string  $defaultRules
     * @return void
     */
    public function __construct($condition, $rules, $defaultRules = [])
    {
        $this->condition = $condition;
        $this->rules = $rules;
        $this->defaultRules = $defaultRules;
    }

    /**
     * Determine if the conditional rules should be added.
	 * 确定是否应该添加条件规则
     *
     * @param  array  $data
     * @return bool
     */
    public function passes(array $data = [])
    {
        return is_callable($this->condition)
                    ? call_user_func($this->condition, new Fluent($data))
                    : $this->condition;
    }

    /**
     * Get the rules.
	 * 得到规则
     *
     * @return array
     */
    public function rules()
    {
        return is_string($this->rules) ? explode('|', $this->rules) : $this->rules;
    }

    /**
     * Get the default rules.
	 * 得到默认规则
     *
     * @return array
     */
    public function defaultRules()
    {
        return is_string($this->defaultRules) ? explode('|', $this->defaultRules) : $this->defaultRules;
    }
}
