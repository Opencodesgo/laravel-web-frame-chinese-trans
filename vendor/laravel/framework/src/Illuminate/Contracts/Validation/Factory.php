<?php
/**
 * Illuminate，契约，验证，工厂
 */

namespace Illuminate\Contracts\Validation;

interface Factory
{
    /**
     * Create a new Validator instance.
	 * 创建新的验证程序实例
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = []);

    /**
     * Register a custom validator extension.
	 * 注册一个自定义验证器扩展
     *
     * @param  string  $rule
     * @param  \Closure|string  $extension
     * @param  string|null  $message
     * @return void
     */
    public function extend($rule, $extension, $message = null);

    /**
     * Register a custom implicit validator extension.
	 * 注册自定义隐式验证器扩展
     *
     * @param  string  $rule
     * @param  \Closure|string  $extension
     * @param  string|null  $message
     * @return void
     */
    public function extendImplicit($rule, $extension, $message = null);

    /**
     * Register a custom implicit validator message replacer.
	 * 注册自定义隐式验证器消息替换程序
     *
     * @param  string  $rule
     * @param  \Closure|string  $replacer
     * @return void
     */
    public function replacer($rule, $replacer);
}
