<?php
/**
 * Illuminate，契约，验证，规则
 */

namespace Illuminate\Contracts\Validation;

interface Rule
{
    /**
     * Determine if the validation rule passes.
	 * 确定验证规则是否通过
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value);

    /**
     * Get the validation error message.
	 * 获取验证错误消息
     *
     * @return string|array
     */
    public function message();
}
