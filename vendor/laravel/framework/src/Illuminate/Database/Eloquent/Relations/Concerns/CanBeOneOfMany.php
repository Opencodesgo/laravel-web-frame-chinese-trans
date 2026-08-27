<?php
/**
 * Illuminate，数据库，Eloquent，关系，问题，能成为众多中的一个吗
 */

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use InvalidArgumentException;

trait CanBeOneOfMany
{
    /**
     * Determines whether the relationship is one-of-many.
	 * 确定该关系是否是众多关系中的一
     *
     * @var bool
     */
    protected $isOneOfMany = false;

    /**
     * The name of the relationship.
	 * 关系的名称
     *
     * @var string
     */
    protected $relationName;

    /**
     * The one of many inner join subselect query builder instance.
	 * 众多内连接子选择查询生成器实例之一
     *
     * @var \Illuminate\Database\Eloquent\Builder|null
     */
    protected $oneOfManySubQuery;

    /**
     * Add constraints for inner join subselect for one of many relationships.
	 * 为多个关系之一的内连接子选择添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $column
     * @param  string|null  $aggregate
     * @return void
     */
    abstract public function addOneOfManySubQueryConstraints(Builder $query, $column = null, $aggregate = null);

    /**
     * Get the columns the determine the relationship groups.
	 * 获取确定关系组的列
     *
     * @return array|string
     */
    abstract public function getOneOfManySubQuerySelectColumns();

    /**
     * Add join query constraints for one of many relationships.
	 * 为多个关系中的一个添加连接查询约束
     *
     * @param  \Illuminate\Database\Query\JoinClause  $join
     * @return void
     */
    abstract public function addOneOfManyJoinSubQueryConstraints(JoinClause $join);

    /**
     * Indicate that the relation is a single result of a larger one-to-many relationship.
	 * 表明该关系是一个更大的一对多关系的单个结果
     *
     * @param  string|array|null  $column
     * @param  string|\Closure|null  $aggregate
     * @param  string|null  $relation
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function ofMany($column = 'id', $aggregate = 'MAX', $relation = null)
    {
        $this->isOneOfMany = true;

        $this->relationName = $relation ?: $this->getDefaultOneOfManyJoinAlias(
            $this->guessRelationship()
        );

        $keyName = $this->query->getModel()->getKeyName();

        $columns = is_string($columns = $column) ? [
            $column => $aggregate,
            $keyName => $aggregate,
        ] : $column;

        if (! array_key_exists($keyName, $columns)) {
            $columns[$keyName] = 'MAX';
        }

        if ($aggregate instanceof Closure) {
            $closure = $aggregate;
        }

        foreach ($columns as $column => $aggregate) {
            if (! in_array(strtolower($aggregate), ['min', 'max'])) {
                throw new InvalidArgumentException("Invalid aggregate [{$aggregate}] used within ofMany relation. Available aggregates: MIN, MAX");
            }

            $subQuery = $this->newOneOfManySubQuery(
                $this->getOneOfManySubQuerySelectColumns(),
                $column, $aggregate
            );

            if (isset($previous)) {
                $this->addOneOfManyJoinSubQuery($subQuery, $previous['subQuery'], $previous['column']);
            }

            if (isset($closure)) {
                $closure($subQuery);
            }

            if (! isset($previous)) {
                $this->oneOfManySubQuery = $subQuery;
            }

            if (array_key_last($columns) == $column) {
                $this->addOneOfManyJoinSubQuery($this->query, $subQuery, $column);
            }

            $previous = [
                'subQuery' => $subQuery,
                'column' => $column,
            ];
        }

        $this->addConstraints();

        $columns = $this->query->getQuery()->columns;

        if (is_null($columns) || $columns === ['*']) {
            $this->select([$this->qualifyColumn('*')]);
        }

        return $this;
    }

    /**
     * Indicate that the relation is the latest single result of a larger one-to-many relationship.
	 * 表明该关系是一个较大的一对多关系的最新单个结果
     *
     * @param  string|array|null  $column
     * @param  string|null  $relation
     * @return $this
     */
    public function latestOfMany($column = 'id', $relation = null)
    {
        return $this->ofMany(collect(Arr::wrap($column))->mapWithKeys(function ($column) {
            return [$column => 'MAX'];
        })->all(), 'MAX', $relation);
    }

    /**
     * Indicate that the relation is the oldest single result of a larger one-to-many relationship.
	 * 表明该关系是较大的一对多关系中最古老的单个结果
     *
     * @param  string|array|null  $column
     * @param  string|null  $relation
     * @return $this
     */
    public function oldestOfMany($column = 'id', $relation = null)
    {
        return $this->ofMany(collect(Arr::wrap($column))->mapWithKeys(function ($column) {
            return [$column => 'MIN'];
        })->all(), 'MIN', $relation);
    }

    /**
     * Get the default alias for the one of many inner join clause.
	 * 获取多个内部连接子句之一的默认别名
     *
     * @param  string  $relation
     * @return string
     */
    protected function getDefaultOneOfManyJoinAlias($relation)
    {
        return $relation == $this->query->getModel()->getTable()
            ? $relation.'_of_many'
            : $relation;
    }

    /**
     * Get a new query for the related model, grouping the query by the given column, often the foreign key of the relationship.
	 * 获取相关模型的新查询，根据给定的列（通常是关系的外键）对查询进行分组。
     *
     * @param  string|array  $groupBy
     * @param  string|null  $column
     * @param  string|null  $aggregate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newOneOfManySubQuery($groupBy, $column = null, $aggregate = null)
    {
        $subQuery = $this->query->getModel()
            ->newQuery()
            ->withoutGlobalScopes($this->removedScopes());

        foreach (Arr::wrap($groupBy) as $group) {
            $subQuery->groupBy($this->qualifyRelatedColumn($group));
        }

        if (! is_null($column)) {
            $subQuery->selectRaw($aggregate.'('.$subQuery->getQuery()->grammar->wrap($subQuery->qualifyColumn($column)).') as '.$subQuery->getQuery()->grammar->wrap($column.'_aggregate'));
        }

        $this->addOneOfManySubQueryConstraints($subQuery, $groupBy, $column, $aggregate);

        return $subQuery;
    }

    /**
     * Add the join subquery to the given query on the given column and the relationship's foreign key.
	 * 将连接子查询添加到给定列和关系的外键上的给定查询中
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $parent
     * @param  \Illuminate\Database\Eloquent\Builder  $subQuery
     * @param  string  $on
     * @return void
     */
    protected function addOneOfManyJoinSubQuery(Builder $parent, Builder $subQuery, $on)
    {
        $parent->beforeQuery(function ($parent) use ($subQuery, $on) {
            $subQuery->applyBeforeQueryCallbacks();

            $parent->joinSub($subQuery, $this->relationName, function ($join) use ($on) {
                $join->on($this->qualifySubSelectColumn($on.'_aggregate'), '=', $this->qualifyRelatedColumn($on));

                $this->addOneOfManyJoinSubQueryConstraints($join, $on);
            });
        });
    }

    /**
     * Merge the relationship query joins to the given query builder.
	 * 将关系查询连接合并到给定的查询生成器
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    protected function mergeOneOfManyJoinsTo(Builder $query)
    {
        $query->getQuery()->beforeQueryCallbacks = $this->query->getQuery()->beforeQueryCallbacks;

        $query->applyBeforeQueryCallbacks();
    }

    /**
     * Get the query builder that will contain the relationship constraints.
	 * 获取将包含关系约束的查询生成器
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getRelationQuery()
    {
        return $this->isOneOfMany()
            ? $this->oneOfManySubQuery
            : $this->query;
    }

    /**
     * Get the one of many inner join subselect builder instance.
	 * 获取多个内部连接子选择构建器实例中的一个
     *
     * @return \Illuminate\Database\Eloquent\Builder|void
     */
    public function getOneOfManySubQuery()
    {
        return $this->oneOfManySubQuery;
    }

    /**
     * Get the qualified column name for the one-of-many relationship using the subselect join query's alias.
	 * 使用子选择连接查询的别名获取一对多关系的限定列名
     *
     * @param  string  $column
     * @return string
     */
    public function qualifySubSelectColumn($column)
    {
        return $this->getRelationName().'.'.last(explode('.', $column));
    }

    /**
     * Qualify related column using the related table name if it is not already qualified.
	 * 如果相关列尚未限定，请使用相关表名限定相关列。
     *
     * @param  string  $column
     * @return string
     */
    protected function qualifyRelatedColumn($column)
    {
        return str_contains($column, '.') ? $column : $this->query->getModel()->getTable().'.'.$column;
    }

    /**
     * Guess the "hasOne" relationship's name via backtrace.
	 * 通过回溯猜测"hasOne"关系的名称
     *
     * @return string
     */
    protected function guessRelationship()
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
    }

    /**
     * Determine whether the relationship is a one-of-many relationship.
	 * 确定该关系是否是"一对多"关系
     *
     * @return bool
     */
    public function isOneOfMany()
    {
        return $this->isOneOfMany;
    }

    /**
     * Get the name of the relationship.
	 * 获取关系的名称
     *
     * @return string
     */
    public function getRelationName()
    {
        return $this->relationName;
    }
}
