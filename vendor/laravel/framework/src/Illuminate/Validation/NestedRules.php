<?php
/**
 * Illuminate，验证，Nested 规则
 */

namespace Illuminate\Validation;

use Illuminate\Support\Arr;

class NestedRules
{
    /**
     * The callback to execute.
	 * 要执行的回调
     *
     * @var callable
     */
    protected $callback;

    /**
     * Create a new nested rule instance.
	 * 创建一个新的嵌套规则实例
     *
     * @param  callable  $callback
     * @return void
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Compile the callback into an array of rules.
	 * 将回调函数编译成一个规则数组
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $data
     * @return \stdClass
     */
    public function compile($attribute, $value, $data = null)
    {
        $rules = call_user_func($this->callback, $value, $attribute, $data);

        $parser = new ValidationRuleParser(
            Arr::undot(Arr::wrap($data))
        );

        if (is_array($rules) && Arr::isAssoc($rules)) {
            $nested = [];

            foreach ($rules as $key => $rule) {
                $nested[$attribute.'.'.$key] = $rule;
            }

            return $parser->explode($nested);
        }

        return $parser->explode([$attribute => $rules]);
    }
}
