<?php
/**
 * Illuminate，验证，规则，包含
 */

namespace Illuminate\Validation\Rules;

class In
{
    /**
     * The name of the rule.
	 * 规则名称
     */
    protected $rule = 'in';

    /**
     * The accepted values.
	 * 接受的值
     *
     * @var array
     */
    protected $values;

    /**
     * Create a new in rule instance.
	 * 创建新的规则实例
     *
     * @param  array  $values
     * @return void
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Convert the rule to a validation string.
	 * 将规则转换为验证字符串
     *
     * @return string
     *
     * @see \Illuminate\Validation\ValidationRuleParser::parseParameters
     */
    public function __toString()
    {
        $values = array_map(function ($value) {
            return '"'.str_replace('"', '""', $value).'"';
        }, $this->values);

        return $this->rule.':'.implode(',', $values);
    }
}
