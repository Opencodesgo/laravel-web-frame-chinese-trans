<?php
/**
 * Illuminate，数据库，Eloquent，问题，查询关系特征
 */

namespace Illuminate\Database\Eloquent\Concerns;

use BadMethodCallException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait QueriesRelationships
{
    /**
     * Add a relationship count / exists condition to the query.
	 * 向查询添加关系计数/存在条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation|string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     *
     * @throws \RuntimeException
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        if (is_string($relation)) {
            if (str_contains($relation, '.')) {
                return $this->hasNested($relation, $operator, $count, $boolean, $callback);
            }

            $relation = $this->getRelationWithoutConstraints($relation);
        }

        if ($relation instanceof MorphTo) {
            return $this->hasMorph($relation, ['*'], $operator, $count, $boolean, $callback);
        }

        // If we only need to check for the existence of the relation, then we can optimize
        // the subquery to only run a "where exists" clause instead of this full "count"
        // clause. This will make these queries run much faster compared with a count.
		// 如果我们只需要检查关系是否存在，然后我们可以优化子查询只运行"where exists"子句。
        $method = $this->canUseExistsForExistenceCheck($operator, $count)
                        ? 'getRelationExistenceQuery'
                        : 'getRelationExistenceCountQuery';

        $hasQuery = $relation->{$method}(
            $relation->getRelated()->newQueryWithoutRelationships(), $this
        );

        // Next we will call any given callback as an "anonymous" scope so they can get the
        // proper logical grouping of the where clauses if needed by this Eloquent query
        // builder. Then, we will be ready to finalize and return this query instance.
		// 接下来，我们将调用任何给定的回调作为"匿名"作用域。
        if ($callback) {
            $hasQuery->callScope($callback);
        }

        return $this->addHasWhere(
            $hasQuery, $relation, $operator, $count, $boolean
        );
    }

    /**
     * Add nested relationship count / exists conditions to the query.
	 * 向查询添加嵌套关系count / exists条件
     *
     * Sets up recursive call to whereHas until we finish the nested relation.
     *
     * @param  string  $relations
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    protected function hasNested($relations, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
    {
        $relations = explode('.', $relations);

        $doesntHave = $operator === '<' && $count === 1;

        if ($doesntHave) {
            $operator = '>=';
            $count = 1;
        }

        $closure = function ($q) use (&$closure, &$relations, $operator, $count, $callback) {
            // In order to nest "has", we need to add count relation constraints on the
            // callback Closure. We'll do this by simply passing the Closure its own
            // reference to itself so it calls itself recursively on each segment.
			// 为了嵌套"has"，我们需要在上添加计数关系约束。
            count($relations) > 1
                ? $q->whereHas(array_shift($relations), $closure)
                : $q->has(array_shift($relations), $operator, $count, 'and', $callback);
        };

        return $this->has(array_shift($relations), $doesntHave ? '<' : '>=', 1, $boolean, $closure);
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
	 * 使用"或"向查询添加关系计数/存在条件
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orHas($relation, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or');
    }

    /**
     * Add a relationship count / exists condition to the query.
	 * 向查询添加关系计数/存在条件
     *
     * @param  string  $relation
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null)
    {
        return $this->has($relation, '<', 1, $boolean, $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with an "or".
	 * 使用"或"向查询添加关系计数/存在条件
     *
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orDoesntHave($relation)
    {
        return $this->doesntHave($relation, 'or');
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
	 * 使用where子句向查询添加关系count / exists条件
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
	 * 使用where子句向查询添加关系count / exists条件
     *
     * Also load the relationship with same condition.
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function withWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->whereHas(Str::before($relation, ':'), $callback, $operator, $count)
            ->with($callback ? [$relation => fn ($query) => $callback($query)] : $relation);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
	 * 使用where子句和"或"向查询添加关系count / exists条件
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
	 * 使用where子句向查询添加关系count / exists条件
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereDoesntHave($relation, Closure $callback = null)
    {
        return $this->doesntHave($relation, 'and', $callback);
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
	 * 使用where子句和"或"向查询添加关系count / exists条件
     *
     * @param  string  $relation
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereDoesntHave($relation, Closure $callback = null)
    {
        return $this->doesntHave($relation, 'or', $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
	 * 向查询添加一个多态关系计数/存在条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        if (is_string($relation)) {
            $relation = $this->getRelationWithoutConstraints($relation);
        }

        $types = (array) $types;

        if ($types === ['*']) {
            $types = $this->model->newModelQuery()->distinct()->pluck($relation->getMorphType())->filter()->all();
        }

        foreach ($types as &$type) {
            $type = Relation::getMorphedModel($type) ?? $type;
        }

        return $this->where(function ($query) use ($relation, $callback, $operator, $count, $types) {
            foreach ($types as $type) {
                $query->orWhere(function ($query) use ($relation, $callback, $operator, $count, $type) {
                    $belongsTo = $this->getBelongsToRelation($relation, $type);

                    if ($callback) {
                        $callback = function ($query) use ($callback, $type) {
                            return $callback($query, $type);
                        };
                    }

                    $query->where($this->qualifyColumn($relation->getMorphType()), '=', (new $type)->getMorphClass())
                                ->whereHas($belongsTo, $callback, $operator, $count);
                });
            }
        }, null, null, $boolean);
    }

    /**
     * Get the BelongsTo relationship for a single polymorphic type.
	 * 获取单个多态类型的BelongsTo关系
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo  $relation
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    protected function getBelongsToRelation(MorphTo $relation, $type)
    {
        $belongsTo = Relation::noConstraints(function () use ($relation, $type) {
            return $this->model->belongsTo(
                $type,
                $relation->getForeignKeyName(),
                $relation->getOwnerKeyName()
            );
        });

        $belongsTo->getQuery()->mergeConstraintsFrom($relation->getQuery());

        return $belongsTo;
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
	 * 使用"或"向查询添加多态关系计数/存在条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  string  $operator
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orHasMorph($relation, $types, $operator = '>=', $count = 1)
    {
        return $this->hasMorph($relation, $types, $operator, $count, 'or');
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query.
	 * 向查询添加一个多态关系计数/存在条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function doesntHaveMorph($relation, $types, $boolean = 'and', Closure $callback = null)
    {
        return $this->hasMorph($relation, $types, '<', 1, $boolean, $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with an "or".
	 * 使用"或"向查询添加多态关系计数/存在条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orDoesntHaveMorph($relation, $types)
    {
        return $this->doesntHaveMorph($relation, $types, 'or');
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
	 * 使用where子句向查询添加一个多态关系count / exists条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  \Closure|null  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->hasMorph($relation, $types, $operator, $count, 'and', $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
	 * 使用where子句和"或"向查询添加一个多态关系count / exists条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  \Closure|null  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereHasMorph($relation, $types, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->hasMorph($relation, $types, $operator, $count, 'or', $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses.
	 * 使用where子句向查询添加一个多态关系count / exists条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereDoesntHaveMorph($relation, $types, Closure $callback = null)
    {
        return $this->doesntHaveMorph($relation, $types, 'and', $callback);
    }

    /**
     * Add a polymorphic relationship count / exists condition to the query with where clauses and an "or".
	 * 使用where子句和"或"向查询添加一个多态关系count / exists条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereDoesntHaveMorph($relation, $types, Closure $callback = null)
    {
        return $this->doesntHaveMorph($relation, $types, 'or', $callback);
    }

    /**
     * Add a basic where clause to a relationship query.
	 * 向关系查询添加一个基本的where子句
     *
     * @param  string  $relation
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereRelation($relation, $column, $operator = null, $value = null)
    {
        return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
            if ($column instanceof Closure) {
                $column($query);
            } else {
                $query->where($column, $operator, $value);
            }
        });
    }

    /**
     * Add an "or where" clause to a relationship query.
	 * 向关系查询添加"or where"子句
     *
     * @param  string  $relation
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereRelation($relation, $column, $operator = null, $value = null)
    {
        return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
            if ($column instanceof Closure) {
                $column($query);
            } else {
                $query->where($column, $operator, $value);
            }
        });
    }

    /**
     * Add a polymorphic relationship condition to the query with a where clause.
	 * 使用where子句向查询添加多态关系条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereMorphRelation($relation, $types, $column, $operator = null, $value = null)
    {
        return $this->whereHasMorph($relation, $types, function ($query) use ($column, $operator, $value) {
            $query->where($column, $operator, $value);
        });
    }

    /**
     * Add a polymorphic relationship condition to the query with an "or where" clause.
	 * 使用"or where"子句向查询添加多态关系条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  string|array  $types
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereMorphRelation($relation, $types, $column, $operator = null, $value = null)
    {
        return $this->orWhereHasMorph($relation, $types, function ($query) use ($column, $operator, $value) {
            $query->where($column, $operator, $value);
        });
    }

    /**
     * Add a morph-to relationship condition to the query.
	 * 向查询添加一个morphto关系条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereMorphedTo($relation, $model, $boolean = 'and')
    {
        if (is_string($relation)) {
            $relation = $this->getRelationWithoutConstraints($relation);
        }

        if (is_string($model)) {
            $morphMap = Relation::morphMap();

            if (! empty($morphMap) && in_array($model, $morphMap)) {
                $model = array_search($model, $morphMap, true);
            }

            return $this->where($relation->getMorphType(), $model, null, $boolean);
        }

        return $this->where(function ($query) use ($relation, $model) {
            $query->where($relation->getMorphType(), $model->getMorphClass())
                ->where($relation->getForeignKeyName(), $model->getKey());
        }, null, null, $boolean);
    }

    /**
     * Add a not morph-to relationship condition to the query.
	 * 向查询添加一个not morphto关系条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function whereNotMorphedTo($relation, $model, $boolean = 'and')
    {
        if (is_string($relation)) {
            $relation = $this->getRelationWithoutConstraints($relation);
        }

        if (is_string($model)) {
            $morphMap = Relation::morphMap();

            if (! empty($morphMap) && in_array($model, $morphMap)) {
                $model = array_search($model, $morphMap, true);
            }

            return $this->whereNot($relation->getMorphType(), '<=>', $model, $boolean);
        }

        return $this->whereNot(function ($query) use ($relation, $model) {
            $query->where($relation->getMorphType(), '<=>', $model->getMorphClass())
                ->where($relation->getForeignKeyName(), '<=>', $model->getKey());
        }, null, null, $boolean);
    }

    /**
     * Add a morph-to relationship condition to the query with an "or where" clause.
	 * 使用"or where"子句向查询添加一个morphto关系条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereMorphedTo($relation, $model)
    {
        return $this->whereMorphedTo($relation, $model, 'or');
    }

    /**
     * Add a not morph-to relationship condition to the query with an "or where" clause.
	 * 使用"or where"子句向查询添加一个not morphto关系条件
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphTo|string  $relation
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function orWhereNotMorphedTo($relation, $model)
    {
        return $this->whereNotMorphedTo($relation, $model, 'or');
    }

    /**
     * Add a "belongs to" relationship where clause to the query.
	 * 向查询添加一个"属于"where关系子句
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection<\Illuminate\Database\Eloquent\Model>  $related
     * @param  string|null  $relationshipName
     * @param  string  $boolean
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\RelationNotFoundException
     */
    public function whereBelongsTo($related, $relationshipName = null, $boolean = 'and')
    {
        if (! $related instanceof Collection) {
            $relatedCollection = $related->newCollection([$related]);
        } else {
            $relatedCollection = $related;

            $related = $relatedCollection->first();
        }

        if ($relatedCollection->isEmpty()) {
            throw new InvalidArgumentException('Collection given to whereBelongsTo method may not be empty.');
        }

        if ($relationshipName === null) {
            $relationshipName = Str::camel(class_basename($related));
        }

        try {
            $relationship = $this->model->{$relationshipName}();
        } catch (BadMethodCallException $exception) {
            throw RelationNotFoundException::make($this->model, $relationshipName);
        }

        if (! $relationship instanceof BelongsTo) {
            throw RelationNotFoundException::make($this->model, $relationshipName, BelongsTo::class);
        }

        $this->whereIn(
            $relationship->getQualifiedForeignKeyName(),
            $relatedCollection->pluck($relationship->getOwnerKeyName())->toArray(),
            $boolean,
        );

        return $this;
    }

    /**
     * Add an "BelongsTo" relationship with an "or where" clause to the query.
	 * 向查询中添加带有"or where"子句的“BelongsTo”关系
     *
     * @param  \Illuminate\Database\Eloquent\Model  $related
     * @param  string|null  $relationshipName
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function orWhereBelongsTo($related, $relationshipName = null)
    {
        return $this->whereBelongsTo($related, $relationshipName, 'or');
    }

    /**
     * Add subselect queries to include an aggregate value for a relationship.
	 * 添加子选择查询以包含关系的聚合值
     *
     * @param  mixed  $relations
     * @param  string  $column
     * @param  string  $function
     * @return $this
     */
    public function withAggregate($relations, $column, $function = null)
    {
        if (empty($relations)) {
            return $this;
        }

        if (is_null($this->query->columns)) {
            $this->query->select([$this->query->from.'.*']);
        }

        $relations = is_array($relations) ? $relations : [$relations];

        foreach ($this->parseWithRelations($relations) as $name => $constraints) {
            // First we will determine if the name has been aliased using an "as" clause on the name
            // and if it has we will extract the actual relationship name and the desired name of
            // the resulting column. This allows multiple aggregates on the same relationships.
			// 首先，我们将使用名称上的"as"子句确定该名称是否已使用别名。
            $segments = explode(' ', $name);

            unset($alias);

            if (count($segments) === 3 && Str::lower($segments[1]) === 'as') {
                [$name, $alias] = [$segments[0], $segments[2]];
            }

            $relation = $this->getRelationWithoutConstraints($name);

            if ($function) {
                $hashedColumn = $this->getRelationHashedColumn($column, $relation);

                $wrappedColumn = $this->getQuery()->getGrammar()->wrap(
                    $column === '*' ? $column : $relation->getRelated()->qualifyColumn($hashedColumn)
                );

                $expression = $function === 'exists' ? $wrappedColumn : sprintf('%s(%s)', $function, $wrappedColumn);
            } else {
                $expression = $column;
            }

            // Here, we will grab the relationship sub-query and prepare to add it to the main query
            // as a sub-select. First, we'll get the "has" query and use that to get the relation
            // sub-query. We'll format this relationship name and append this column if needed.
			// 在这里，我们将获取关系子查询并准备将其添加到主查询中。
            $query = $relation->getRelationExistenceQuery(
                $relation->getRelated()->newQuery(), $this, new Expression($expression)
            )->setBindings([], 'select');

            $query->callScope($constraints);

            $query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();

            // If the query contains certain elements like orderings / more than one column selected
            // then we will remove those elements from the query so that it will execute properly
            // when given to the database. Otherwise, we may receive SQL errors or poor syntax.
			// 如果查询包含某些元素，如orderings /所选的多个列。
            $query->orders = null;
            $query->setBindings([], 'order');

            if (count($query->columns) > 1) {
                $query->columns = [$query->columns[0]];
                $query->bindings['select'] = [];
            }

            // Finally, we will make the proper column alias to the query and run this sub-select on
            // the query builder. Then, we will return the builder instance back to the developer
            // for further constraint chaining that needs to take place on the query as needed.
			// 最后，我们将为查询设置适当的列别名，并运行此子选择。
            $alias ??= Str::snake(
                preg_replace('/[^[:alnum:][:space:]_]/u', '', "$name $function $column")
            );

            if ($function === 'exists') {
                $this->selectRaw(
                    sprintf('exists(%s) as %s', $query->toSql(), $this->getQuery()->grammar->wrap($alias)),
                    $query->getBindings()
                )->withCasts([$alias => 'bool']);
            } else {
                $this->selectSub(
                    $function ? $query : $query->limit(1),
                    $alias
                );
            }
        }

        return $this;
    }

    /**
     * Get the relation hashed column name for the given column and relation.
	 * 获取给定列和关系的关系哈希列名称
     *
     * @param  string  $column
     * @param  \Illuminate\Database\Eloquent\Relations\Relationship  $relation
     * @return string
     */
    protected function getRelationHashedColumn($column, $relation)
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $this->getQuery()->from === $relation->getQuery()->getQuery()->from
            ? "{$relation->getRelationCountHash(false)}.$column"
            : $column;
    }

    /**
     * Add subselect queries to count the relations.
	 * 添加子选择查询来统计关系
     *
     * @param  mixed  $relations
     * @return $this
     */
    public function withCount($relations)
    {
        return $this->withAggregate(is_array($relations) ? $relations : func_get_args(), '*', 'count');
    }

    /**
     * Add subselect queries to include the max of the relation's column.
	 * 添加子选择查询以包含关系列的最大
     *
     * @param  string|array  $relation
     * @param  string  $column
     * @return $this
     */
    public function withMax($relation, $column)
    {
        return $this->withAggregate($relation, $column, 'max');
    }

    /**
     * Add subselect queries to include the min of the relation's column.
	 * 添加子选择查询以包含关系列的最小值
     *
     * @param  string|array  $relation
     * @param  string  $column
     * @return $this
     */
    public function withMin($relation, $column)
    {
        return $this->withAggregate($relation, $column, 'min');
    }

    /**
     * Add subselect queries to include the sum of the relation's column.
	 * 添加子选择查询以包含关系列的总和
     *
     * @param  string|array  $relation
     * @param  string  $column
     * @return $this
     */
    public function withSum($relation, $column)
    {
        return $this->withAggregate($relation, $column, 'sum');
    }

    /**
     * Add subselect queries to include the average of the relation's column.
	 * 添加子选择查询以包含关系列的平均值
     *
     * @param  string|array  $relation
     * @param  string  $column
     * @return $this
     */
    public function withAvg($relation, $column)
    {
        return $this->withAggregate($relation, $column, 'avg');
    }

    /**
     * Add subselect queries to include the existence of related models.
	 * 添加子选择查询以包含相关模型的存在性
     *
     * @param  string|array  $relation
     * @return $this
     */
    public function withExists($relation)
    {
        return $this->withAggregate($relation, '*', 'exists');
    }

    /**
     * Add the "has" condition where clause to the query.
	 * 向查询中添加"has"条件where子句
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $hasQuery
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    protected function addHasWhere(Builder $hasQuery, Relation $relation, $operator, $count, $boolean)
    {
        $hasQuery->mergeConstraintsFrom($relation->getQuery());

        return $this->canUseExistsForExistenceCheck($operator, $count)
                ? $this->addWhereExistsQuery($hasQuery->toBase(), $boolean, $operator === '<' && $count === 1)
                : $this->addWhereCountQuery($hasQuery->toBase(), $operator, $count, $boolean);
    }

    /**
     * Merge the where constraints from another query to the current query.
	 * 将来自另一个查询的where约束合并到当前查询
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $from
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function mergeConstraintsFrom(Builder $from)
    {
        $whereBindings = $from->getQuery()->getRawBindings()['where'] ?? [];

        $wheres = $from->getQuery()->from !== $this->getQuery()->from
            ? $this->requalifyWhereTables(
                $from->getQuery()->wheres,
                $from->getQuery()->from,
                $this->getModel()->getTable()
            ) : $from->getQuery()->wheres;

        // Here we have some other query that we want to merge the where constraints from. We will
        // copy over any where constraints on the query as well as remove any global scopes the
        // query might have removed. Then we will return ourselves with the finished merging.
		// 这里我们有一些其他的查询我们想要合并where约束。
        return $this->withoutGlobalScopes(
            $from->removedScopes()
        )->mergeWheres(
            $wheres, $whereBindings
        );
    }

    /**
     * Updates the table name for any columns with a new qualified name.
	 * 使用新的限定名更新任何列的表名
     *
     * @param  array  $wheres
     * @param  string  $from
     * @param  string  $to
     * @return array
     */
    protected function requalifyWhereTables(array $wheres, string $from, string $to): array
    {
        return collect($wheres)->map(function ($where) use ($from, $to) {
            return collect($where)->map(function ($value) use ($from, $to) {
                return is_string($value) && str_starts_with($value, $from.'.')
                    ? $to.'.'.Str::afterLast($value, '.')
                    : $value;
            });
        })->toArray();
    }

    /**
     * Add a sub-query count clause to this query.
	 * 向该查询添加子查询计数子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @return $this
     */
    protected function addWhereCountQuery(QueryBuilder $query, $operator = '>=', $count = 1, $boolean = 'and')
    {
        $this->query->addBinding($query->getBindings(), 'where');

        return $this->where(
            new Expression('('.$query->toSql().')'),
            $operator,
            is_numeric($count) ? new Expression($count) : $count,
            $boolean
        );
    }

    /**
     * Get the "has relation" base query instance.
	 * 获取"有关系"基本查询实例
     *
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function getRelationWithoutConstraints($relation)
    {
        return Relation::noConstraints(function () use ($relation) {
            return $this->getModel()->{$relation}();
        });
    }

    /**
     * Check if we can run an "exists" query to optimize performance.
	 * 检查我们是否可以运行"exists"查询来优化性能
     *
     * @param  string  $operator
     * @param  int  $count
     * @return bool
     */
    protected function canUseExistsForExistenceCheck($operator, $count)
    {
        return ($operator === '>=' || $operator === '<') && $count === 1;
    }
}
