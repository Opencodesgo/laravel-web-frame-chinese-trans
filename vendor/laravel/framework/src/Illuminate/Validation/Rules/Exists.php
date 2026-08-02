<?php
/**
 * Illuminate，验证，规则，存在
 */

namespace Illuminate\Validation\Rules;

class Exists
{
    use DatabaseRule;

    /**
     * Convert the rule to a validation string.
	 * 将规则转换为验证字符串
     *
     * @return string
     */
    public function __toString()
    {
        return rtrim(sprintf('exists:%s,%s,%s',
            $this->table,
            $this->column,
            $this->formatWheres()
        ), ',');
    }
}
