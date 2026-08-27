<?php
/**
 * Illuminate，数据库，查询，表达式
 */

namespace Illuminate\Database\Query;

class Expression
{
    /**
     * The value of the expression.
	 * 表达式值
     *
     * @var mixed
     */
    protected $value;

    /**
     * Create a new raw query expression.
	 * 创建一个新的原始查询表达式
     *
     * @param  mixed  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value of the expression.
	 * 获取表达式的值
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the value of the expression.
	 * 获取表达式的值
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }
}
