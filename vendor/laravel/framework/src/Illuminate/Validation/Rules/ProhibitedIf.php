<?php
/**
 * Illuminate，验证，规则，如果禁止
 */

namespace Illuminate\Validation\Rules;

use Closure;
use InvalidArgumentException;

class ProhibitedIf
{
    /**
     * The condition that validates the attribute.
	 * 验证属性的条件
     *
     * @var \Closure|bool
     */
    public $condition;

    /**
     * Create a new prohibited validation rule based on a condition.
	 * 根据条件创建新的禁止验证规则
     *
     * @param  \Closure|bool  $condition
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($condition)
    {
        if ($condition instanceof Closure || is_bool($condition)) {
            $this->condition = $condition;
        } else {
            throw new InvalidArgumentException('The provided condition must be a callable or boolean.');
        }
    }

    /**
     * Convert the rule to a validation string.
	 * 将规则转换为验证字符串
     *
     * @return string
     */
    public function __toString()
    {
        if (is_callable($this->condition)) {
            return call_user_func($this->condition) ? 'prohibited' : '';
        }

        return $this->condition ? 'prohibited' : '';
    }
}
