<?php
/**
 * Illuminate，验证，规则，存在
 */

namespace Illuminate\Validation\Rules;

use Illuminate\Support\Traits\Conditionable;

class Exists
{
    use Conditionable, DatabaseRule;

    /**
     * Ignore soft deleted models during the existence check.
	 * 在存在性检查期间忽略软删除模型
     *
     * @param  string  $deletedAtColumn
     * @return $this
     */
    public function withoutTrashed($deletedAtColumn = 'deleted_at')
    {
        $this->whereNull($deletedAtColumn);

        return $this;
    }

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
