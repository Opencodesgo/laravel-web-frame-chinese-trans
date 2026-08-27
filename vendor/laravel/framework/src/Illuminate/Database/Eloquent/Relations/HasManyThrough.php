<?php
/**
 * Illuminate，数据库，Eloquent，关系，有许多穿过
 */

namespace Illuminate\Database\Eloquent\Relations;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\SoftDeletes;

class HasManyThrough extends Relation
{
    use InteractsWithDictionary;

    /**
     * The "through" parent model instance.
	 * "through"父模型实例
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $throughParent;

    /**
     * The far parent model instance.
	 * 远父模型实例
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $farParent;

    /**
     * The near key on the relationship.
	 * 关系上的near键
     *
     * @var string
     */
    protected $firstKey;

    /**
     * The far key on the relationship.
	 * 关系上的远键
     *
     * @var string
     */
    protected $secondKey;

    /**
     * The local key on the relationship.
	 * 关系上的本地键
     *
     * @var string
     */
    protected $localKey;

    /**
     * The local key on the intermediary model.
	 * 中间模型上的本地键
     *
     * @var string
     */
    protected $secondLocalKey;

    /**
     * Create a new has many through relationship instance.
	 * 创建一个新的有多个通过关系实例
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Model  $throughParent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @param  string  $secondLocalKey
     * @return void
     */
    public function __construct(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        $this->localKey = $localKey;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->farParent = $farParent;
        $this->throughParent = $throughParent;
        $this->secondLocalKey = $secondLocalKey;

        parent::__construct($query, $throughParent);
    }

    /**
     * Set the base constraints on the relation query.
	 * 在关系查询上设置基本约束
     *
     * @return void
     */
    public function addConstraints()
    {
        $localValue = $this->farParent[$this->localKey];

        $this->performJoin();

        if (static::$constraints) {
            $this->query->where($this->getQualifiedFirstKeyName(), '=', $localValue);
        }
    }

    /**
     * Set the join clause on the query.
	 * 在查询上设置连接子句
     *
     * @param  \Illuminate\Database\Eloquent\Builder|null  $query
     * @return void
     */
    protected function performJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;

        $farKey = $this->getQualifiedFarKeyName();

        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $farKey);

        if ($this->throughParentSoftDeletes()) {
            $query->withGlobalScope('SoftDeletableHasManyThrough', function ($query) {
                $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
            });
        }
    }

    /**
     * Get the fully qualified parent key name.
	 * 获取完全限定父键名
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        return $this->parent->qualifyColumn($this->secondLocalKey);
    }

    /**
     * Determine whether "through" parent of the relation uses Soft Deletes.
	 * 确定关系的"through"父级是否使用软删除
     *
     * @return bool
     */
    public function throughParentSoftDeletes()
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->throughParent));
    }

    /**
     * Indicate that trashed "through" parents should be included in the query.
	 * 指示应该在查询中包含垃圾的"through"父级
     *
     * @return $this
     */
    public function withTrashedParents()
    {
        $this->query->withoutGlobalScope('SoftDeletableHasManyThrough');

        return $this;
    }

    /**
     * Set the constraints for an eager load of the relation.
	 * 为关系的即时加载设置约束
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->farParent, $this->localKey);

        $this->query->{$whereIn}(
            $this->getQualifiedFirstKeyName(), $this->getKeys($models, $this->localKey)
        );
    }

    /**
     * Initialize the relation on a set of models.
	 * 初始化一组模型上的关系
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
	 * 将急切加载的结果与他们的父母匹配
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
		// 有了字典之后，我们可以简单地遍历父模型。
        foreach ($models as $model) {
            if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
                $model->setRelation(
                    $relation, $this->related->newCollection($dictionary[$key])
                );
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
	 * 构建以关系的外键为键的模型字典
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
		// 首先，我们将创建一个由外部键输入的模型字典。
        foreach ($results as $result) {
            $dictionary[$result->laravel_through_key][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the first related model record matching the attributes or instantiate it.
	 * 获取与属性匹配的第一个相关模型记录，或者实例化它。
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes);
        }

        return $instance;
    }

    /**
     * Create or update a related record matching the attributes, and fill it with values.
	 * 创建或更新与属性匹配的相关记录，并用值填充它。
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $instance = $this->firstOrNew($attributes);

        $instance->fill($values)->save();

        return $instance;
    }

    /**
     * Add a basic where clause to the query, and return the first result.
	 * 向查询添加一个基本的where子句，并返回第一个结果。
     *
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->where($column, $operator, $value, $boolean)->first();
    }

    /**
     * Execute the query and get the first related model.
	 * 执行查询并获得第一个相关模型
     *
     * @param  array  $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->take(1)->get($columns);

        return count($results) > 0 ? $results->first() : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
	 * 执行查询并获得第一个结果或抛出异常
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->related));
    }

    /**
     * Execute the query and get the first result or call a callback.
	 * 执行查询并获得第一个结果或调用回调
     *
     * @param  \Closure|array  $columns
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Model|static|mixed
     */
    public function firstOr($columns = ['*'], Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        return $callback();
    }

    /**
     * Find a related model by its primary key.
	 * 根据主键查找相关模型
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        return $this->where(
            $this->getRelated()->getQualifiedKeyName(), '=', $id
        )->first($columns);
    }

    /**
     * Find multiple related models by their primary keys.
	 * 通过主键查找多个相关模型
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }

        return $this->whereIn(
            $this->getRelated()->getQualifiedKeyName(), $ids
        )->get($columns);
    }

    /**
     * Find a related model by its primary key or throw an exception.
	 * 根据主键查找相关模型，否则抛出异常。
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->related), $id);
    }

    /**
     * Find a related model by its primary key or call a callback.
	 * 通过主键查找相关模型或调用回调
     *
     * @param  mixed  $id
     * @param  \Closure|array  $columns
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function findOr($id, $columns = ['*'], Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        $result = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        return $callback();
    }

    /**
     * Get the results of the relationship.
	 * 得到关系的结果
     *
     * @return mixed
     */
    public function getResults()
    {
        return ! is_null($this->farParent->{$this->localKey})
                ? $this->get()
                : $this->related->newCollection();
    }

    /**
     * Execute the query as a "select" statement.
	 * 以"select"语句的形式执行查询
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {
        $builder = $this->prepareQueryBuilder($columns);

        $models = $builder->getModels();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
		// 如果我们真的找到了模型，我们也会急切地加载任何关系。
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Get a paginator for the "select" statement.
	 * 获取"select"语句的分页器
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return $this->query->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
	 * 将给定查询分页到一个简单的分页器中
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return $this->query->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a cursor paginator.
	 * 将给定查询分页到游标分页器中
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $cursorName
     * @param  string|null  $cursor
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return $this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor);
    }

    /**
     * Set the select clause for the relation query.
	 * 为关系查询设置select子句
     *
     * @param  array  $columns
     * @return array
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }

        return array_merge($columns, [$this->getQualifiedFirstKeyName().' as laravel_through_key']);
    }

    /**
     * Chunk the results of the query.
	 * 将查询的结果分块
     *
     * @param  int  $count
     * @param  callable  $callback
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        return $this->prepareQueryBuilder()->chunk($count, $callback);
    }

    /**
     * Chunk the results of a query by comparing numeric IDs.
	 * 通过比较数字id对查询结果进行分组
     *
     * @param  int  $count
     * @param  callable  $callback
     * @param  string|null  $column
     * @param  string|null  $alias
     * @return bool
     */
    public function chunkById($count, callable $callback, $column = null, $alias = null)
    {
        $column ??= $this->getRelated()->getQualifiedKeyName();

        $alias ??= $this->getRelated()->getKeyName();

        return $this->prepareQueryBuilder()->chunkById($count, $callback, $column, $alias);
    }

    /**
     * Get a generator for the given query.
	 * 获取给定查询的生成器
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function cursor()
    {
        return $this->prepareQueryBuilder()->cursor();
    }

    /**
     * Execute a callback over each item while chunking.
	 * 在分块时对每个项执行回调
     *
     * @param  callable  $callback
     * @param  int  $count
     * @return bool
     */
    public function each(callable $callback, $count = 1000)
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
        });
    }

    /**
     * Query lazily, by chunks of the given size.
	 * 按给定大小的块惰性查询
     *
     * @param  int  $chunkSize
     * @return \Illuminate\Support\LazyCollection
     */
    public function lazy($chunkSize = 1000)
    {
        return $this->prepareQueryBuilder()->lazy($chunkSize);
    }

    /**
     * Query lazily, by chunking the results of a query by comparing IDs.
	 * 惰性查询，通过比较id将查询结果分块。
     *
     * @param  int  $chunkSize
     * @param  string|null  $column
     * @param  string|null  $alias
     * @return \Illuminate\Support\LazyCollection
     */
    public function lazyById($chunkSize = 1000, $column = null, $alias = null)
    {
        $column ??= $this->getRelated()->getQualifiedKeyName();

        $alias ??= $this->getRelated()->getKeyName();

        return $this->prepareQueryBuilder()->lazyById($chunkSize, $column, $alias);
    }

    /**
     * Prepare the query builder for query execution.
	 * 为查询执行准备查询生成器
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function prepareQueryBuilder($columns = ['*'])
    {
        $builder = $this->query->applyScopes();

        return $builder->addSelect(
            $this->shouldSelect($builder->getQuery()->columns ? [] : $columns)
        );
    }

    /**
     * Add the constraints for a relationship query.
	 * 为关系查询添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from === $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        if ($parentQuery->getQuery()->from === $this->throughParent->getTable()) {
            return $this->getRelationExistenceQueryForThroughSelfRelation($query, $parentQuery, $columns);
        }

        $this->performJoin($query);

        return $query->select($columns)->whereColumn(
            $this->getQualifiedLocalKeyName(), '=', $this->getQualifiedFirstKeyName()
        );
    }

    /**
     * Add the constraints for a relationship query on the same table.
	 * 为同一表上的关系查询添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $hash.'.'.$this->secondKey);

        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
        }

        $query->getModel()->setTable($hash);

        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from.'.'.$this->localKey, '=', $this->getQualifiedFirstKeyName()
        );
    }

    /**
     * Add the constraints for a relationship query on the same table as the through parent.
	 * 在与through父表相同的表上为关系查询添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForThroughSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $table = $this->throughParent->getTable().' as '.$hash = $this->getRelationCountHash();

        $query->join($table, $hash.'.'.$this->secondLocalKey, '=', $this->getQualifiedFarKeyName());

        if ($this->throughParentSoftDeletes()) {
            $query->whereNull($hash.'.'.$this->throughParent->getDeletedAtColumn());
        }

        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from.'.'.$this->localKey, '=', $hash.'.'.$this->firstKey
        );
    }

    /**
     * Get the qualified foreign key on the related model.
	 * 获取相关模型上的合格外键
     *
     * @return string
     */
    public function getQualifiedFarKeyName()
    {
        return $this->getQualifiedForeignKeyName();
    }

    /**
     * Get the foreign key on the "through" model.
	 * 获取"直通"模式上的外键
     *
     * @return string
     */
    public function getFirstKeyName()
    {
        return $this->firstKey;
    }

    /**
     * Get the qualified foreign key on the "through" model.
	 * 获取"through"模型上的合格外键
     *
     * @return string
     */
    public function getQualifiedFirstKeyName()
    {
        return $this->throughParent->qualifyColumn($this->firstKey);
    }

    /**
     * Get the foreign key on the related model.
	 * 获取相关模型上的外键
     *
     * @return string
     */
    public function getForeignKeyName()
    {
        return $this->secondKey;
    }

    /**
     * Get the qualified foreign key on the related model.
	 * 获取相关模型上的合格外键
     *
     * @return string
     */
    public function getQualifiedForeignKeyName()
    {
        return $this->related->qualifyColumn($this->secondKey);
    }

    /**
     * Get the local key on the far parent model.
	 * 获取远父模型上的本地键
     *
     * @return string
     */
    public function getLocalKeyName()
    {
        return $this->localKey;
    }

    /**
     * Get the qualified local key on the far parent model.
	 * 获取远父模型上的限定本地键
     *
     * @return string
     */
    public function getQualifiedLocalKeyName()
    {
        return $this->farParent->qualifyColumn($this->localKey);
    }

    /**
     * Get the local key on the intermediary model.
	 * 获取中介模型上的本地键
     *
     * @return string
     */
    public function getSecondLocalKeyName()
    {
        return $this->secondLocalKey;
    }
}
