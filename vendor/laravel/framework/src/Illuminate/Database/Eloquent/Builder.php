<?php
/**
 * Illuminate，数据库，Eloquent，生成器
 */

namespace Illuminate\Database\Eloquent;

use BadMethodCallException;
use Closure;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Concerns\QueriesRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use ReflectionClass;
use ReflectionMethod;

/**
 * @property-read HigherOrderBuilderProxy $orWhere
 * @property-read HigherOrderBuilderProxy $whereNot
 * @property-read HigherOrderBuilderProxy $orWhereNot
 *
 * @mixin \Illuminate\Database\Query\Builder
 */
class Builder implements BuilderContract
{
    use BuildsQueries, ForwardsCalls, QueriesRelationships {
        BuildsQueries::sole as baseSole;
    }

    /**
     * The base query builder instance.
	 * 基本查询生成器实例
     *
     * @var \Illuminate\Database\Query\Builder
     */
    protected $query;

    /**
     * The model being queried.
	 * 正在查询的模型
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The relationships that should be eager loaded.
	 * 这种关系应该是充满渴望的
     *
     * @var array
     */
    protected $eagerLoad = [];

    /**
     * All of the globally registered builder macros.
	 * 所有全局注册的构建器宏
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * All of the locally registered builder macros.
	 * 所有本地注册的构建器宏
     *
     * @var array
     */
    protected $localMacros = [];

    /**
     * A replacement for the typical delete function.
	 * 典型删除函数的替代
     *
     * @var \Closure
     */
    protected $onDelete;

    /**
     * The properties that should be returned from query builder.
	 * 应该从查询生成器返回的属性
     *
     * @var string[]
     */
    protected $propertyPassthru = [
        'from',
    ];

    /**
     * The methods that should be returned from query builder.
	 * 应该从查询生成器返回的方法
     *
     * @var string[]
     */
    protected $passthru = [
        'aggregate',
        'average',
        'avg',
        'count',
        'dd',
        'doesntExist',
        'doesntExistOr',
        'dump',
        'exists',
        'existsOr',
        'explain',
        'getBindings',
        'getConnection',
        'getGrammar',
        'implode',
        'insert',
        'insertGetId',
        'insertOrIgnore',
        'insertUsing',
        'max',
        'min',
        'raw',
        'rawValue',
        'sum',
        'toSql',
    ];

    /**
     * Applied global scopes.
	 * 应用全局作用域
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * Removed global scopes.
	 * 删除了全局作用域
     *
     * @var array
     */
    protected $removedScopes = [];

    /**
     * Create a new Eloquent query builder instance.
	 * 创建一个新的Eloquent查询构建器实例
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return void
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Create and return an un-saved model instance.
	 * 创建并返回一个未保存的模型实例
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function make(array $attributes = [])
    {
        return $this->newModelInstance($attributes);
    }

    /**
     * Register a new global scope.
	 * 注册一个新的全局作用域
     *
     * @param  string  $identifier
     * @param  \Illuminate\Database\Eloquent\Scope|\Closure  $scope
     * @return $this
     */
    public function withGlobalScope($identifier, $scope)
    {
        $this->scopes[$identifier] = $scope;

        if (method_exists($scope, 'extend')) {
            $scope->extend($this);
        }

        return $this;
    }

    /**
     * Remove a registered global scope.
	 * 删除已注册的全局作用域
     *
     * @param  \Illuminate\Database\Eloquent\Scope|string  $scope
     * @return $this
     */
    public function withoutGlobalScope($scope)
    {
        if (! is_string($scope)) {
            $scope = get_class($scope);
        }

        unset($this->scopes[$scope]);

        $this->removedScopes[] = $scope;

        return $this;
    }

    /**
     * Remove all or passed registered global scopes.
	 * 删除所有或传递的已注册全局作用域
     *
     * @param  array|null  $scopes
     * @return $this
     */
    public function withoutGlobalScopes(array $scopes = null)
    {
        if (! is_array($scopes)) {
            $scopes = array_keys($this->scopes);
        }

        foreach ($scopes as $scope) {
            $this->withoutGlobalScope($scope);
        }

        return $this;
    }

    /**
     * Get an array of global scopes that were removed from the query.
	 * 获取从查询中删除的全局作用域数组
     *
     * @return array
     */
    public function removedScopes()
    {
        return $this->removedScopes;
    }

    /**
     * Add a where clause on the primary key to the query.
	 * 在查询的主键上添加where子句
     *
     * @param  mixed  $id
     * @return $this
     */
    public function whereKey($id)
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        if (is_array($id) || $id instanceof Arrayable) {
            if (in_array($this->model->getKeyType(), ['int', 'integer'])) {
                $this->query->whereIntegerInRaw($this->model->getQualifiedKeyName(), $id);
            } else {
                $this->query->whereIn($this->model->getQualifiedKeyName(), $id);
            }

            return $this;
        }

        if ($id !== null && $this->model->getKeyType() === 'string') {
            $id = (string) $id;
        }

        return $this->where($this->model->getQualifiedKeyName(), '=', $id);
    }

    /**
     * Add a where clause on the primary key to the query.
	 * 在查询的主键上添加where子句
     *
     * @param  mixed  $id
     * @return $this
     */
    public function whereKeyNot($id)
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        if (is_array($id) || $id instanceof Arrayable) {
            if (in_array($this->model->getKeyType(), ['int', 'integer'])) {
                $this->query->whereIntegerNotInRaw($this->model->getQualifiedKeyName(), $id);
            } else {
                $this->query->whereNotIn($this->model->getQualifiedKeyName(), $id);
            }

            return $this;
        }

        if ($id !== null && $this->model->getKeyType() === 'string') {
            $id = (string) $id;
        }

        return $this->where($this->model->getQualifiedKeyName(), '!=', $id);
    }

    /**
     * Add a basic where clause to the query.
	 * 向查询添加一个基本的where子句
     *
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure && is_null($operator)) {
            $column($query = $this->model->newQueryWithoutRelationships());

            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        } else {
            $this->query->where(...func_get_args());
        }

        return $this;
    }

    /**
     * Add a basic where clause to the query, and return the first result.
	 * 向查询添加一个基本的where子句，并返回第一个结果。
     *
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->where(...func_get_args())->first();
    }

    /**
     * Add an "or where" clause to the query.
	 * 向查询添加"or where"子句
     *
     * @param  \Closure|array|string|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->query->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a basic "where not" clause to the query.
	 * 在查询中添加一个基本的"where not"子句
     *
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereNot($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->where($column, $operator, $value, $boolean.' not');
    }

    /**
     * Add an "or where not" clause to the query.
	 * 在查询中添加"or where not"子句
     *
     * @param  \Closure|array|string|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereNot($column, $operator = null, $value = null)
    {
        return $this->whereNot($column, $operator, $value, 'or');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
	 * 在查询中为时间戳添加"order by"子句
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @return $this
     */
    public function latest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->latest($column);

        return $this;
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @return $this
     */
    public function oldest($column = null)
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->oldest($column);

        return $this;
    }

    /**
     * Create a collection of models from plain arrays.
	 * 从普通数组创建模型集合
     *
     * @param  array  $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(array_map(function ($item) use ($items, $instance) {
            $model = $instance->newFromBuilder($item);

            if (count($items) > 1) {
                $model->preventsLazyLoading = Model::preventsLazyLoading();
            }

            return $model;
        }, $items));
    }

    /**
     * Create a collection of models from a raw query.
	 * 从原始查询创建模型集合
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fromQuery($query, $bindings = [])
    {
        return $this->hydrate(
            $this->query->getConnection()->select($query, $bindings)
        );
    }

    /**
     * Find a model by its primary key.
	 * 根据主键查找模型
     *
     * @param  mixed  $id
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        return $this->whereKey($id)->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
	 * 通过主键查找多个模型
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $ids
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereKey($ids)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
	 * 根据主键查找模型，否则抛出异常。
     *
     * @param  mixed  $id
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static|static[]
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) !== count(array_unique($id))) {
                throw (new ModelNotFoundException)->setModel(
                    get_class($this->model), array_diff($id, $result->modelKeys())
                );
            }

            return $result;
        }

        if (is_null($result)) {
            throw (new ModelNotFoundException)->setModel(
                get_class($this->model), $id
            );
        }

        return $result;
    }

    /**
     * Find a model by its primary key or return fresh model instance.
	 * 通过主键查找模型或返回新的模型实例
     *
     * @param  mixed  $id
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (! is_null($model = $this->find($id, $columns))) {
            return $model;
        }

        return $this->newModelInstance();
    }

    /**
     * Find a model by its primary key or call a callback.
	 * 通过主键查找模型或调用回调
     *
     * @param  mixed  $id
     * @param  \Closure|array|string  $columns
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|mixed
     */
    public function findOr($id, $columns = ['*'], Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        if (! is_null($model = $this->find($id, $columns))) {
            return $model;
        }

        return $callback();
    }

    /**
     * Get the first record matching the attributes or instantiate it.
	 * 获取匹配属性的第一个记录或实例化它
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrNew(array $attributes = [], array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return $this->newModelInstance(array_merge($attributes, $values));
    }

    /**
     * Get the first record matching the attributes or create it.
	 * 获取匹配属性的第一个记录或创建它
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return tap($this->newModelInstance(array_merge($attributes, $values)), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
	 * 创建或更新与属性匹配的记录，并用值填充它。
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return tap($this->firstOrNew($attributes), function ($instance) use ($values) {
            $instance->fill($values)->save();
        });
    }

    /**
     * Execute the query and get the first result or throw an exception.
	 * 执行查询并获得第一个结果或抛出异常
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model|static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * Execute the query and get the first result or call a callback.
	 * 执行查询并获得第一个结果或调用回调
     *
     * @param  \Closure|array|string  $columns
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
     * Execute the query and get the first result if it's the sole matching record.
	 * 如果查询是唯一匹配的记录，则执行查询并获得第一个结果。
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     * @throws \Illuminate\Database\MultipleRecordsFoundException
     */
    public function sole($columns = ['*'])
    {
        try {
            return $this->baseSole($columns);
        } catch (RecordsNotFoundException $exception) {
            throw (new ModelNotFoundException)->setModel(get_class($this->model));
        }
    }

    /**
     * Get a single column's value from the first result of a query.
	 * 从查询的第一个结果中获取单个列的值
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @return mixed
     */
    public function value($column)
    {
        if ($result = $this->first([$column])) {
            return $result->{Str::afterLast($column, '.')};
        }
    }

    /**
     * Get a single column's value from the first result of a query if it's the sole matching record.
	 * 如果查询的第一个结果是唯一匹配的记录，则从该结果中获取单个列的值。
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     * @throws \Illuminate\Database\MultipleRecordsFoundException
     */
    public function soleValue($column)
    {
        return $this->sole([$column])->{Str::afterLast($column, '.')};
    }

    /**
     * Get a single column's value from the first result of the query or throw an exception.
	 * 从查询的第一个结果中获取单个列的值，否则抛出异常。
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function valueOrFail($column)
    {
        return $this->firstOrFail([$column])->{Str::afterLast($column, '.')};
    }

    /**
     * Execute the query as a "select" statement.
	 * 以"select"语句的形式执行查询
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = ['*'])
    {
        $builder = $this->applyScopes();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
		// 如果我们真的找到了模型，我们也会急切地加载任何关系。
        if (count($models = $builder->getModels($columns)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
    }

    /**
     * Get the hydrated models without eager loading.
	 * 得到水合模型，没有急切加载。
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model[]|static[]
     */
    public function getModels($columns = ['*'])
    {
        return $this->model->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }

    /**
     * Eager load the relationships for the models.
	 * 预先加载模型的关系
     *
     * @param  array  $models
     * @return array
     */
    public function eagerLoadRelations(array $models)
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            // For nested eager loads we'll skip loading them here and they will be set as an
            // eager load on the query to retrieve the relation so that they will be eager
            // loaded on that query, because that is where they get hydrated as models.
			// 对于嵌套的急切加载，我们将跳过加载它们，它们将被设置对查询进行即时加载以检索关系。
            if (! str_contains($name, '.')) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }

        return $models;
    }

    /**
     * Eagerly load the relationship on a set of models.
	 * 急切地将关系加载到一组模型上
     *
     * @param  array  $models
     * @param  string  $name
     * @param  \Closure  $constraints
     * @return array
     */
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
		// 首先，我们将"备份"查询上现有的where条件。
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $constraints($relation);

        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        return $relation->match(
            $relation->initRelation($models, $name),
            $relation->getEager(), $name
        );
    }

    /**
     * Get the relation instance for the given relation name.
	 * 获取给定关系名称的关系实例
     *
     * @param  string  $name
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getRelation($name)
    {
        // We want to run a relationship query without any constrains so that we will
        // not have to remove these where clauses manually which gets really hacky
        // and error prone. We don't want constraints because we add eager ones.
		// 我们希望运行一个没有任何约束的关系查询。
        $relation = Relation::noConstraints(function () use ($name) {
            try {
                return $this->getModel()->newInstance()->$name();
            } catch (BadMethodCallException $e) {
                throw RelationNotFoundException::make($this->getModel(), $name);
            }
        });

        $nested = $this->relationsNestedUnder($name);

        // If there are nested relationships set on the query, we will put those onto
        // the query instances so that they can be handled after this relationship
        // is loaded. In this way they will all trickle down as they are loaded.
		// 如果查询上设置了嵌套关系，
        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }

        return $relation;
    }

    /**
     * Get the deeply nested relations for a given top-level relation.
	 * 获取给定顶级关系的深度嵌套关系
     *
     * @param  string  $relation
     * @return array
     */
    protected function relationsNestedUnder($relation)
    {
        $nested = [];

        // We are basically looking for any relationships that are nested deeper than
        // the given top-level relationship. We will just check for any relations
        // that start with the given top relations and adds them to our arrays.
        foreach ($this->eagerLoad as $name => $constraints) {
            if ($this->isNestedUnder($relation, $name)) {
                $nested[substr($name, strlen($relation.'.'))] = $constraints;
            }
        }

        return $nested;
    }

    /**
     * Determine if the relationship is nested.
	 * 确定关系是否嵌套
     *
     * @param  string  $relation
     * @param  string  $name
     * @return bool
     */
    protected function isNestedUnder($relation, $name)
    {
        return str_contains($name, '.') && str_starts_with($name, $relation.'.');
    }

    /**
     * Get a lazy collection for the given query.
	 * 获取给定查询的惰性集合
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function cursor()
    {
        return $this->applyScopes()->query->cursor()->map(function ($record) {
            return $this->newModelInstance()->newFromBuilder($record);
        });
    }

    /**
     * Add a generic "order by" clause if the query doesn't already have one.
	 * 如果查询还没有通用的"order by"子句，则添加一个。
     *
     * @return void
     */
    protected function enforceOrderBy()
    {
        if (empty($this->query->orders) && empty($this->query->unionOrders)) {
            $this->orderBy($this->model->getQualifiedKeyName(), 'asc');
        }
    }

    /**
     * Get a collection with the values of a given column.
	 * 获取包含给定列值的集合
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($column, $key = null)
    {
        $results = $this->toBase()->pluck($column, $key);

        // If the model has a mutator for the requested column, we will spin through
        // the results and mutate the values so that the mutated version of these
        // columns are returned as you would expect from these Eloquent models.
		// 如果模型对请求的列有一个mutator，
        if (! $this->model->hasGetMutator($column) &&
            ! $this->model->hasCast($column) &&
            ! in_array($column, $this->model->getDates())) {
            return $results;
        }

        return $results->map(function ($value) use ($column) {
            return $this->model->newFromBuilder([$column => $value])->{$column};
        });
    }

    /**
     * Paginate the given query.
	 * 对给定查询进行分页
     *
     * @param  int|null|\Closure  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = $this->toBase()->getCountForPagination();

        $perPage = ($perPage instanceof Closure
            ? $perPage($total)
            : $perPage
        ) ?: $this->model->getPerPage();

        $results = $total
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Paginate the given query into a simple paginator.
	 * 将给定查询分页到一个简单的分页器中
     *
     * @param  int|null  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: $this->model->getPerPage();

        // Next we will set the limit and offset for this query so that when we get the
        // results we get the proper section of results. Then, we'll create the full
        // paginator instances for these results with the given page and per page.
		// 接下来，我们将为此查询设置限制和偏移量，以便我们得到了适当的部分结果。
        $this->skip(($page - 1) * $perPage)->take($perPage + 1);

        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Paginate the given query into a cursor paginator.
	 * 将给定查询分页到游标分页器中
     *
     * @param  int|null  $perPage
     * @param  array|string  $columns
     * @param  string  $cursorName
     * @param  \Illuminate\Pagination\Cursor|string|null  $cursor
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $perPage = $perPage ?: $this->model->getPerPage();

        return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
    }

    /**
     * Ensure the proper order by required for cursor pagination.
	 * 确保游标分页所需的正确顺序
     *
     * @param  bool  $shouldReverse
     * @return \Illuminate\Support\Collection
     */
    protected function ensureOrderForCursorPagination($shouldReverse = false)
    {
        if (empty($this->query->orders) && empty($this->query->unionOrders)) {
            $this->enforceOrderBy();
        }

        if ($shouldReverse) {
            $this->query->orders = collect($this->query->orders)->map(function ($order) {
                $order['direction'] = $order['direction'] === 'asc' ? 'desc' : 'asc';

                return $order;
            })->toArray();
        }

        if ($this->query->unionOrders) {
            return collect($this->query->unionOrders);
        }

        return collect($this->query->orders);
    }

    /**
     * Save a new model and return the instance.
	 * 保存新模型并返回实例
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|$this
     */
    public function create(array $attributes = [])
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Save a new model and return the instance. Allow mass-assignment.
	 * 保存新模型并返回实例。允许质量确定。
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|$this
     */
    public function forceCreate(array $attributes)
    {
        return $this->model->unguarded(function () use ($attributes) {
            return $this->newModelInstance()->create($attributes);
        });
    }

    /**
     * Update records in the database.
	 * 更新数据库中的记录
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        return $this->toBase()->update($this->addUpdatedAtColumn($values));
    }

    /**
     * Insert new records or update the existing ones.
	 * 插入新记录或更新现有记录
     *
     * @param  array  $values
     * @param  array|string  $uniqueBy
     * @param  array|null  $update
     * @return int
     */
    public function upsert(array $values, $uniqueBy, $update = null)
    {
        if (empty($values)) {
            return 0;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        if (is_null($update)) {
            $update = array_keys(reset($values));
        }

        return $this->toBase()->upsert(
            $this->addTimestampsToUpsertValues($values),
            $uniqueBy,
            $this->addUpdatedAtToUpsertColumns($update)
        );
    }

    /**
     * Update the column's update timestamp.
	 * 更新列的更新时间戳
     *
     * @param  string|null  $column
     * @return int|false
     */
    public function touch($column = null)
    {
        $time = $this->model->freshTimestamp();

        if ($column) {
            return $this->toBase()->update([$column => $time]);
        }

        $column = $this->model->getUpdatedAtColumn();

        if (! $this->model->usesTimestamps() || is_null($column)) {
            return false;
        }

        return $this->toBase()->update([$column => $time]);
    }

    /**
     * Increment a column's value by a given amount.
	 * 将列的值增加给定的量
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        return $this->toBase()->increment(
            $column, $amount, $this->addUpdatedAtColumn($extra)
        );
    }

    /**
     * Decrement a column's value by a given amount.
	 * 将列的值递减给定的量
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        return $this->toBase()->decrement(
            $column, $amount, $this->addUpdatedAtColumn($extra)
        );
    }

    /**
     * Add the "updated at" column to an array of values.
	 * 将"updated at"列添加到值数组中
     *
     * @param  array  $values
     * @return array
     */
    protected function addUpdatedAtColumn(array $values)
    {
        if (! $this->model->usesTimestamps() ||
            is_null($this->model->getUpdatedAtColumn())) {
            return $values;
        }

        $column = $this->model->getUpdatedAtColumn();

        $values = array_merge(
            [$column => $this->model->freshTimestampString()],
            $values
        );

        $segments = preg_split('/\s+as\s+/i', $this->query->from);

        $qualifiedColumn = end($segments).'.'.$column;

        $values[$qualifiedColumn] = Arr::get($values, $qualifiedColumn, $values[$column]);

        unset($values[$column]);

        return $values;
    }

    /**
     * Add timestamps to the inserted values.
	 * 向插入的值添加时间戳
     *
     * @param  array  $values
     * @return array
     */
    protected function addTimestampsToUpsertValues(array $values)
    {
        if (! $this->model->usesTimestamps()) {
            return $values;
        }

        $timestamp = $this->model->freshTimestampString();

        $columns = array_filter([
            $this->model->getCreatedAtColumn(),
            $this->model->getUpdatedAtColumn(),
        ]);

        foreach ($columns as $column) {
            foreach ($values as &$row) {
                $row = array_merge([$column => $timestamp], $row);
            }
        }

        return $values;
    }

    /**
     * Add the "updated at" column to the updated columns.
	 * 将"updated at"列添加到更新的列中
     *
     * @param  array  $update
     * @return array
     */
    protected function addUpdatedAtToUpsertColumns(array $update)
    {
        if (! $this->model->usesTimestamps()) {
            return $update;
        }

        $column = $this->model->getUpdatedAtColumn();

        if (! is_null($column) &&
            ! array_key_exists($column, $update) &&
            ! in_array($column, $update)) {
            $update[] = $column;
        }

        return $update;
    }

    /**
     * Delete records from the database.
	 * 删除数据库中的记录
     *
     * @return mixed
     */
    public function delete()
    {
        if (isset($this->onDelete)) {
            return call_user_func($this->onDelete, $this);
        }

        return $this->toBase()->delete();
    }

    /**
     * Run the default delete function on the builder.
	 * 在构建器上运行默认的删除函数
     *
     * Since we do not apply scopes here, the row will actually be deleted.
     *
     * @return mixed
     */
    public function forceDelete()
    {
        return $this->query->delete();
    }

    /**
     * Register a replacement for the default delete function.
	 * 注册一个默认删除函数的替代品
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function onDelete(Closure $callback)
    {
        $this->onDelete = $callback;
    }

    /**
     * Determine if the given model has a scope.
	 * 确定给定模型是否具有作用域
     *
     * @param  string  $scope
     * @return bool
     */
    public function hasNamedScope($scope)
    {
        return $this->model && $this->model->hasNamedScope($scope);
    }

    /**
     * Call the given local model scopes.
	 * 调用给定的局部模型范围
     *
     * @param  array|string  $scopes
     * @return static|mixed
     */
    public function scopes($scopes)
    {
        $builder = $this;

        foreach (Arr::wrap($scopes) as $scope => $parameters) {
            // If the scope key is an integer, then the scope was passed as the value and
            // the parameter list is empty, so we will format the scope name and these
            // parameters here. Then, we'll be ready to call the scope on the model.
            if (is_int($scope)) {
                [$scope, $parameters] = [$parameters, []];
            }

            // Next we'll pass the scope callback to the callScope method which will take
            // care of grouping the "wheres" properly so the logical order doesn't get
            // messed up when adding scopes. Then we'll return back out the builder.
            $builder = $builder->callNamedScope(
                $scope, Arr::wrap($parameters)
            );
        }

        return $builder;
    }

    /**
     * Apply the scopes to the Eloquent builder instance and return it.
	 * 将作用域应用于Eloquent构建器实例并返回它
     *
     * @return static
     */
    public function applyScopes()
    {
        if (! $this->scopes) {
            return $this;
        }

        $builder = clone $this;

        foreach ($this->scopes as $identifier => $scope) {
            if (! isset($builder->scopes[$identifier])) {
                continue;
            }

            $builder->callScope(function (self $builder) use ($scope) {
                // If the scope is a Closure we will just go ahead and call the scope with the
                // builder instance. The "callScope" method will properly group the clauses
                // that are added to this query so "where" clauses maintain proper logic.
                if ($scope instanceof Closure) {
                    $scope($builder);
                }

                // If the scope is a scope object, we will call the apply method on this scope
                // passing in the builder and the model instance. After we run all of these
                // scopes we will return back the builder instance to the outside caller.
                if ($scope instanceof Scope) {
                    $scope->apply($builder, $this->getModel());
                }
            });
        }

        return $builder;
    }

    /**
     * Apply the given scope on the current builder instance.
	 * 在当前构建器实例上应用给定的范围
     *
     * @param  callable  $scope
     * @param  array  $parameters
     * @return mixed
     */
    protected function callScope(callable $scope, array $parameters = [])
    {
        array_unshift($parameters, $this);

        $query = $this->getQuery();

        // We will keep track of how many wheres are on the query before running the
        // scope so that we can properly group the added scope constraints in the
        // query as their own isolated nested where statement and avoid issues.
        $originalWhereCount = is_null($query->wheres)
                    ? 0 : count($query->wheres);

        $result = $scope(...$parameters) ?? $this;

        if (count((array) $query->wheres) > $originalWhereCount) {
            $this->addNewWheresWithinGroup($query, $originalWhereCount);
        }

        return $result;
    }

    /**
     * Apply the given named scope on the current builder instance.
	 * 在当前构建器实例上应用给定的命名范围
     *
     * @param  string  $scope
     * @param  array  $parameters
     * @return mixed
     */
    protected function callNamedScope($scope, array $parameters = [])
    {
        return $this->callScope(function (...$parameters) use ($scope) {
            return $this->model->callNamedScope($scope, $parameters);
        }, $parameters);
    }

    /**
     * Nest where conditions by slicing them at the given where count.
	 * 通过在给定的where计数处切片来嵌套where条件
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $originalWhereCount
     * @return void
     */
    protected function addNewWheresWithinGroup(QueryBuilder $query, $originalWhereCount)
    {
        // Here, we totally remove all of the where clauses since we are going to
        // rebuild them as nested queries by slicing the groups of wheres into
        // their own sections. This is to prevent any confusing logic order.
        $allWheres = $query->wheres;

        $query->wheres = [];

        $this->groupWhereSliceForScope(
            $query, array_slice($allWheres, 0, $originalWhereCount)
        );

        $this->groupWhereSliceForScope(
            $query, array_slice($allWheres, $originalWhereCount)
        );
    }

    /**
     * Slice where conditions at the given offset and add them to the query as a nested condition.
	 * 将给定偏移量处的where条件切片，并将它们作为嵌套条件添加到查询中。
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $whereSlice
     * @return void
     */
    protected function groupWhereSliceForScope(QueryBuilder $query, $whereSlice)
    {
        $whereBooleans = collect($whereSlice)->pluck('boolean');

        // Here we'll check if the given subset of where clauses contains any "or"
        // booleans and in this case create a nested where expression. That way
        // we don't add any unnecessary nesting thus keeping the query clean.
        if ($whereBooleans->contains('or')) {
            $query->wheres[] = $this->createNestedWhere(
                $whereSlice, $whereBooleans->first()
            );
        } else {
            $query->wheres = array_merge($query->wheres, $whereSlice);
        }
    }

    /**
     * Create a where array with nested where conditions.
	 * 创建一个嵌套where条件的where数组
     *
     * @param  array  $whereSlice
     * @param  string  $boolean
     * @return array
     */
    protected function createNestedWhere($whereSlice, $boolean = 'and')
    {
        $whereGroup = $this->getQuery()->forNestedWhere();

        $whereGroup->wheres = $whereSlice;

        return ['type' => 'Nested', 'query' => $whereGroup, 'boolean' => $boolean];
    }

    /**
     * Set the relationships that should be eager loaded.
	 * 设置应该急于加载的关系
     *
     * @param  string|array  $relations
     * @param  string|\Closure|null  $callback
     * @return $this
     */
    public function with($relations, $callback = null)
    {
        if ($callback instanceof Closure) {
            $eagerLoad = $this->parseWithRelations([$relations => $callback]);
        } else {
            $eagerLoad = $this->parseWithRelations(is_string($relations) ? func_get_args() : $relations);
        }

        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);

        return $this;
    }

    /**
     * Prevent the specified relations from being eager loaded.
	 * 防止指定的关系被紧急加载
     *
     * @param  mixed  $relations
     * @return $this
     */
    public function without($relations)
    {
        $this->eagerLoad = array_diff_key($this->eagerLoad, array_flip(
            is_string($relations) ? func_get_args() : $relations
        ));

        return $this;
    }

    /**
     * Set the relationships that should be eager loaded while removing any previously added eager loading specifications.
	 * 设置应该立即加载的关系，同时删除任何先前添加的立即加载规范。
     *
     * @param  mixed  $relations
     * @return $this
     */
    public function withOnly($relations)
    {
        $this->eagerLoad = [];

        return $this->with($relations);
    }

    /**
     * Create a new instance of the model being queried.
	 * 创建正在查询的模型的新实例
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes)->setConnection(
            $this->query->getConnection()->getName()
        );
    }

    /**
     * Parse a list of relations into individuals.
	 * 将关系列表解析为个体
     *
     * @param  array  $relations
     * @return array
     */
    protected function parseWithRelations(array $relations)
    {
        if ($relations === []) {
            return [];
        }

        $results = [];

        foreach ($this->prepareNestedWithRelationships($relations) as $name => $constraints) {
            // We need to separate out any nested includes, which allows the developers
            // to load deep relationships using "dots" without stating each level of
            // the relationship with its own key in the array of eager-load names.
			// 我们需要分离出任何嵌套的include，这使得开发人员使用"点"来加载深层关系，而不用说明每个层次
            $results = $this->addNestedWiths($name, $results);

            $results[$name] = $constraints;
        }

        return $results;
    }

    /**
     * Prepare nested with relationships.
	 * 准备嵌套的关系
     *
     * @param  array  $relations
     * @param  string  $prefix
     * @return array
     */
    protected function prepareNestedWithRelationships($relations, $prefix = '')
    {
        $preparedRelationships = [];

        if ($prefix !== '') {
            $prefix .= '.';
        }

        // If any of the relationships are formatted with the [$attribute => array()]
        // syntax, we shall loop over the nested relations and prepend each key of
        // this array while flattening into the traditional dot notation format.
        foreach ($relations as $key => $value) {
            if (! is_string($key) || ! is_array($value)) {
                continue;
            }

            [$attribute, $attributeSelectConstraint] = $this->parseNameAndAttributeSelectionConstraint($key);

            $preparedRelationships = array_merge(
                $preparedRelationships,
                ["{$prefix}{$attribute}" => $attributeSelectConstraint],
                $this->prepareNestedWithRelationships($value, "{$prefix}{$attribute}"),
            );

            unset($relations[$key]);
        }

        // We now know that the remaining relationships are in a dot notation format
        // and may be a string or Closure. We'll loop over them and ensure all of
        // the present Closures are merged + strings are made into constraints.
        foreach ($relations as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                [$key, $value] = $this->parseNameAndAttributeSelectionConstraint($value);
            }

            $preparedRelationships[$prefix.$key] = $this->combineConstraints([
                $value,
                $preparedRelationships[$prefix.$key] ?? static function () {
                    //
                },
            ]);
        }

        return $preparedRelationships;
    }

    /**
     * Combine an array of constraints into a single constraint.
	 * 将约束数组合并为单个约束
     *
     * @param  array  $constraints
     * @return \Closure
     */
    protected function combineConstraints(array $constraints)
    {
        return function ($builder) use ($constraints) {
            foreach ($constraints as $constraint) {
                $builder = $constraint($builder) ?? $builder;
            }

            return $builder;
        };
    }

    /**
     * Parse the attribute select constraints from the name.
	 * 从名称解析属性select constraints
     *
     * @param  string  $name
     * @return array
     */
    protected function parseNameAndAttributeSelectionConstraint($name)
    {
        return str_contains($name, ':')
            ? $this->createSelectWithConstraint($name)
            : [$name, static function () {
                //
            }];
    }

    /**
     * Create a constraint to select the given columns for the relation.
	 * 创建一个约束，为关系选择给定的列。
     *
     * @param  string  $name
     * @return array
     */
    protected function createSelectWithConstraint($name)
    {
        return [explode(':', $name)[0], static function ($query) use ($name) {
            $query->select(array_map(static function ($column) use ($query) {
                if (str_contains($column, '.')) {
                    return $column;
                }

                return $query instanceof BelongsToMany
                        ? $query->getRelated()->getTable().'.'.$column
                        : $column;
            }, explode(',', explode(':', $name)[1])));
        }];
    }

    /**
     * Parse the nested relationships in a relation.
	 * 解析关系中的嵌套关系
     *
     * @param  string  $name
     * @param  array  $results
     * @return array
     */
    protected function addNestedWiths($name, $results)
    {
        $progress = [];

        // If the relation has already been set on the result array, we will not set it
        // again, since that would override any constraints that were already placed
        // on the relationships. We will only set the ones that are not specified.
		// 如果已经在结果数组上设置了关系，同样，因为那会覆盖任何已经放置的约束。
        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;

            if (! isset($results[$last = implode('.', $progress)])) {
                $results[$last] = static function () {
                    //
                };
            }
        }

        return $results;
    }

    /**
     * Apply query-time casts to the model instance.
	 * 对模型实例应用查询时强制转换
     *
     * @param  array  $casts
     * @return $this
     */
    public function withCasts($casts)
    {
        $this->model->mergeCasts($casts);

        return $this;
    }

    /**
     * Get the underlying query builder instance.
	 * 获取底层查询生成器实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the underlying query builder instance.
	 * 设置底层查询生成器实例
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get a base query builder instance.
	 * 获取基本查询生成器实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function toBase()
    {
        return $this->applyScopes()->getQuery();
    }

    /**
     * Get the relationships being eagerly loaded.
	 * 让关系被急切地加载
     *
     * @return array
     */
    public function getEagerLoads()
    {
        return $this->eagerLoad;
    }

    /**
     * Set the relationships being eagerly loaded.
	 * 设置急切加载的关系
     *
     * @param  array  $eagerLoad
     * @return $this
     */
    public function setEagerLoads(array $eagerLoad)
    {
        $this->eagerLoad = $eagerLoad;

        return $this;
    }

    /**
     * Indicate that the given relationships should not be eagerly loaded.
	 * 指示不应该急切加载给定的关系
     *
     * @param  array  $relations
     * @return $this
     */
    public function withoutEagerLoad(array $relations)
    {
        $relations = array_diff(array_keys($this->model->getRelations()), $relations);

        return $this->with($relations);
    }

    /**
     * Flush the relationships being eagerly loaded.
	 * 刷新正在加载的关系
     *
     * @return $this
     */
    public function withoutEagerLoads()
    {
        return $this->setEagerLoads([]);
    }

    /**
     * Get the default key name of the table.
	 * 获取表的默认键名
     *
     * @return string
     */
    protected function defaultKeyName()
    {
        return $this->getModel()->getKeyName();
    }

    /**
     * Get the model instance being queried.
	 * 获取正在查询的模型实例
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set a model instance for the model being queried.
	 * 为正在查询的模型设置一个模型实例
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->query->from($model->getTable());

        return $this;
    }

    /**
     * Qualify the given column name by the model's table.
	 * 根据模型的表限定给定的列
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @return string
     */
    public function qualifyColumn($column)
    {
        return $this->model->qualifyColumn($column);
    }

    /**
     * Qualify the given columns with the model's table.
	 * 用模型的表限定给定的列
     *
     * @param  array|\Illuminate\Database\Query\Expression  $columns
     * @return array
     */
    public function qualifyColumns($columns)
    {
        return $this->model->qualifyColumns($columns);
    }

    /**
     * Get the given macro by name.
	 * 按名称获取给定的宏
     *
     * @param  string  $name
     * @return \Closure
     */
    public function getMacro($name)
    {
        return Arr::get($this->localMacros, $name);
    }

    /**
     * Checks if a macro is registered.
	 * 检查是否注册了宏
     *
     * @param  string  $name
     * @return bool
     */
    public function hasMacro($name)
    {
        return isset($this->localMacros[$name]);
    }

    /**
     * Get the given global macro by name.
	 * 按名称获取给定的全局宏
     *
     * @param  string  $name
     * @return \Closure
     */
    public static function getGlobalMacro($name)
    {
        return Arr::get(static::$macros, $name);
    }

    /**
     * Checks if a global macro is registered.
	 * 检查是否注册了全局宏
     *
     * @param  string  $name
     * @return bool
     */
    public static function hasGlobalMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Dynamically access builder proxies.
	 * 动态访问构建器代理
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($key)
    {
        if (in_array($key, ['orWhere', 'whereNot', 'orWhereNot'])) {
            return new HigherOrderBuilderProxy($this, $key);
        }

        if (in_array($key, $this->propertyPassthru)) {
            return $this->toBase()->{$key};
        }

        throw new Exception("Property [{$key}] does not exist on the Eloquent builder instance.");
    }

    /**
     * Dynamically handle calls into the query instance.
	 * 动态处理对查询实例的调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($method === 'macro') {
            $this->localMacros[$parameters[0]] = $parameters[1];

            return;
        }

        if ($this->hasMacro($method)) {
            array_unshift($parameters, $this);

            return $this->localMacros[$method](...$parameters);
        }

        if (static::hasGlobalMacro($method)) {
            $callable = static::$macros[$method];

            if ($callable instanceof Closure) {
                $callable = $callable->bindTo($this, static::class);
            }

            return $callable(...$parameters);
        }

        if ($this->hasNamedScope($method)) {
            return $this->callNamedScope($method, $parameters);
        }

        if (in_array($method, $this->passthru)) {
            return $this->toBase()->{$method}(...$parameters);
        }

        $this->forwardCallTo($this->query, $method, $parameters);

        return $this;
    }

    /**
     * Dynamically handle calls into the query instance.
	 * 动态处理对查询实例的调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($method, $parameters)
    {
        if ($method === 'macro') {
            static::$macros[$parameters[0]] = $parameters[1];

            return;
        }

        if ($method === 'mixin') {
            return static::registerMixin($parameters[0], $parameters[1] ?? true);
        }

        if (! static::hasGlobalMacro($method)) {
            static::throwBadMethodCallException($method);
        }

        $callable = static::$macros[$method];

        if ($callable instanceof Closure) {
            $callable = $callable->bindTo(null, static::class);
        }

        return $callable(...$parameters);
    }

    /**
     * Register the given mixin with the builder.
	 * 在构建器中注册给定的mixin
     *
     * @param  string  $mixin
     * @param  bool  $replace
     * @return void
     */
    protected static function registerMixin($mixin, $replace)
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($replace || ! static::hasGlobalMacro($method->name)) {
                $method->setAccessible(true);

                static::macro($method->name, $method->invoke($mixin));
            }
        }
    }

    /**
     * Clone the Eloquent query builder.
	 * 克隆Eloquent查询生成器
     *
     * @return static
     */
    public function clone()
    {
        return clone $this;
    }

    /**
     * Force a clone of the underlying query builder when cloning.
	 * 克隆时强制克隆底层查询生成器
     *
     * @return void
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }
}
