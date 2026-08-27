<?php
/**
 * Illuminate，契约，验证，调用规则
 */

namespace Illuminate\Contracts\Validation;

interface InvokableRule
{
    /**
     * Run the validation rule.
	 * 运行验证规则
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail);
}
