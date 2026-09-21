<?php
/**
 * Illuminate，契约，验证，验证器感知规则
 */

namespace Illuminate\Contracts\Validation;

interface ValidatorAwareRule
{
    /**
     * Set the current validator.
	 * 设置当前验证程序
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator);
}
