<?php
/**
 * Illuminate，契约，数据库，Eloquent，支持部分关系
 */

namespace Illuminate\Contracts\Database\Eloquent;

interface SupportsPartialRelations
{
    /**
     * Indicate that the relation is a single result of a larger one-to-many relationship.
	 * 表明该关系是一个更大的一对多关系的单个结果
     *
     * @param  string|null  $column
     * @param  string|\Closure|null  $aggregate
     * @param  string  $relation
     * @return $this
     */
    public function ofMany($column = 'id', $aggregate = 'MAX', $relation = null);

    /**
     * Determine whether the relationship is a one-of-many relationship.
	 * 确定该关系是否是"一对多"关系
     *
     * @return bool
     */
    public function isOneOfMany();

    /**
     * Get the one of many inner join subselect query builder instance.
	 * 获取多个内部连接子选择查询生成器实例中的一个
     *
     * @return \Illuminate\Database\Eloquent\Builder|void
     */
    public function getOneOfManySubQuery();
}
