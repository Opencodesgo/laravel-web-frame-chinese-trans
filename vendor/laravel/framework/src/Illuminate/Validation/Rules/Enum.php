<?php
/**
 * Illuminate，验证，规则，枚举
 */

namespace Illuminate\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use TypeError;

class Enum implements Rule
{
    /**
     * The type of the enum.
	 * 枚举的类型
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new rule instance.
	 * 创建一个新的规则实例
     *
     * @param  string  $type
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
	 * 确定验证规则是否通过
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value instanceof $this->type) {
            return true;
        }

        if (is_null($value) || ! function_exists('enum_exists') || ! enum_exists($this->type) || ! method_exists($this->type, 'tryFrom')) {
            return false;
        }

        try {
            return ! is_null($this->type::tryFrom($value));
        } catch (TypeError $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
	 * 获取验证错误消息
     *
     * @return array
     */
    public function message()
    {
        $message = trans('validation.enum');

        return $message === 'validation.enum'
            ? ['The selected :attribute is invalid.']
            : $message;
    }
}
