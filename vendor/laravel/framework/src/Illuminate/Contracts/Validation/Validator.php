<?php
/**
 * Illuminate，契约，验证，验证器
 */

namespace Illuminate\Contracts\Validation;

use Illuminate\Contracts\Support\MessageProvider;

interface Validator extends MessageProvider
{
    /**
     * Run the validator's rules against its data.
	 * 针对其数据运行验证器的规则
     *
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate();

    /**
     * Get the attributes and values that were validated.
	 * 获取已验证的属性和值
     *
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validated();

    /**
     * Determine if the data fails the validation rules.
	 * 确定数据是否不符合验证规则
     *
     * @return bool
     */
    public function fails();

    /**
     * Get the failed validation rules.
	 * 获取失败的验证规则
     *
     * @return array
     */
    public function failed();

    /**
     * Add conditions to a given field based on a Closure.
	 * 根据Closure向给定字段添加条件
     *
     * @param  string|array  $attribute
     * @param  string|array  $rules
     * @param  callable  $callback
     * @return $this
     */
    public function sometimes($attribute, $rules, callable $callback);

    /**
     * Add an after validation callback.
	 * 添加一个验证后回调
     *
     * @param  callable|string  $callback
     * @return $this
     */
    public function after($callback);

    /**
     * Get all of the validation error messages.
	 * 获取所有验证错误消息
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function errors();
}
