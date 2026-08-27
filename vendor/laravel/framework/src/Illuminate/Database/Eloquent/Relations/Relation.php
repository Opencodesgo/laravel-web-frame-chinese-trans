<?php
/**
 * Illuminate，数据库，Eloquent，关系，关系抽象类
 */

namespace Illuminate\Database\Eloquent\Relations;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;

abstract class Relation implements BuilderContract
{
    use ForwardsCalls, Macroable {
        Macroable::__call as macroCall;
    }

    /**
     * The Eloquent query builder instance.
	 * Eloquent查询生成器实例
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * The parent model instance.
	 * 父模型实例
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parent;

    /**
     * The related model instance.
	 * 相关的模型实例
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $related;

    /**
     * Indicates if the relation is adding constraints.
	 * 指示关系是否正在添加约束
     *
     * @var bool
     */
    protected static $constraints = true;

    /**
     * An array to map class names to their morph names in the database.
	 * 一个数组，用于将类名映射到数据库中的类名。
     *
     * @var array
     */
    public static $morphMap = [];

    /**
     * Prevents morph relationships without a morph map.
	 * 防止没有变形映射的变形关系
     *
     * @var bool
     */
    protected static $requireMorphMap = false;

    /**
     * The count of self joins.
	 * 自连接的数
     *
     * @var int
     */
    protected static $selfJoinCount = 0;

    /**
     * Create a new relation instance.
	 * 创建新的关系实例
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @return void
     */
    public function __construct(Builder $query, Model $parent)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();

        $this->addConstraints();
    }

    /**
     * Run a callback with constraints disabled on the relation.
	 * 在禁用关系约束的情况下运行回调
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    public static function noConstraints(Closure $callback)
    {
        $previous = static::$constraints;

        static::$constraints = false;

        // When resetting the relation where clause, we want to shift the first element
        // off of the bindings, leaving only the constraints that the developers put
        // as "extra" on the relationships, and not original relation constraints.
		// 当重置where子句的关系时，我们想要移动第一个元素。
        try {
            return $callback();
        } finally {
            static::$constraints = $previous;
        }
    }

    /**
     * Set the base constraints on the relation query.
	 * 在关系查询上设置基本约束
     *
     * @return void
     */
    abstract public function addConstraints();

    /**
     * Set the constraints for an eager load of the relation.
	 * 为关系的即时加载设置约束
     *
     * @param  array  $models
     * @return void
     */
    abstract public function addEagerConstraints(array $models);

    /**
     * Initialize the relation on a set of models.
	 * 初始化一组模型上的关系
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    abstract public function initRelation(array $models, $relation);

    /**
     * Match the eagerly loaded results to their parents.
	 * 将急切加载的结果与他们的父母匹配
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    abstract public function match(array $models, Collection $results, $relation);

    /**
     * Get the results of the relationship.
	 * 得到关系的结果
     *
     * @return mixed
     */
    abstract public function getResults();

    /**
     * Get the relationship for eager loading.
	 * 获取急于加载的关系
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEager()
    {
        return $this->get();
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
        $result = $this->take(2)->get($columns);

        $count = $result->count();

        if ($count === 0) {
            throw (new ModelNotFoundException)->setModel(get_class($this->related));
        }

        if ($count > 1) {
            throw new MultipleRecordsFoundException($count);
        }

        return $result->first();
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
        return $this->query->get($columns);
    }

    /**
     * Touch all of the related models for the relationship.
	 * 触摸关系的所有相关模型
     *
     * @return void
     */
    public function touch()
    {
        $model = $this->getRelated();

        if (! $model::isIgnoringTouch()) {
            $this->rawUpdate([
                $model->getUpdatedAtColumn() => $model->freshTimestampString(),
            ]);
        }
    }

    /**
     * Run a raw update against the base query.
	 * 对基本查询运行原始更新
     *
     * @param  array  $attributes
     * @return int
     */
    public function rawUpdate(array $attributes = [])
    {
        return $this->query->withoutGlobalScopes()->update($attributes);
    }

    /**
     * Add the constraints for a relationship count query.
	 * 为关系计数查询添加约束
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceCountQuery(Builder $query, Builder $parentQuery)
    {
        return $this->getRelationExistenceQuery(
            $query, $parentQuery, new Expression('count(*)')
        )->setBindings([], 'select');
    }

    /**
     * Add the constraints for an internal relationship existence query.
	 * 为内部关系存在性查询添加约束
     *
     * Essentially, these queries compare on column names like whereColumn.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return $query->select($columns)->whereColumn(
            $this->getQualifiedParentKeyName(), '=', $this->getExistenceCompareKey()
        );
    }

    /**
     * Get a relationship join table hash.
	 * 获取关系连接表散列
     *
     * @param  bool  $incrementJoinCount
     * @return string
     */
    public function getRelationCountHash($incrementJoinCount = true)
    {
        return 'laravel_reserved_'.($incrementJoinCount ? static::$selfJoinCount++ : static::$selfJoinCount);
    }

    /**
     * Get all of the primary keys for an array of models.
	 * 获取模型数组的所有主键
     *
     * @param  array  $models
     * @param  string|null  $key
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        return collect($models)->map(function ($value) use ($key) {
            return $key ? $value->getAttribute($key) : $value->getKey();
        })->values()->unique(null, true)->sort()->all();
    }

    /**
     * Get the query builder that will contain the relationship constraints.
	 * 获取将包含关系约束的查询生成器
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getRelationQuery()
    {
        return $this->query;
    }

    /**
     * Get the underlying query for the relation.
	 * 获取关系的基础查询
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the base query builder driving the Eloquent builder.
	 * 获取驱动Eloquent构建器的基本查询构建器
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getBaseQuery()
    {
        return $this->query->getQuery();
    }

    /**
     * Get a base query builder instance.
	 * 获取基本查询生成器实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function toBase()
    {
        return $this->query->toBase();
    }

    /**
     * Get the parent model of the relation.
	 * 获取关系的父模型
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the fully qualified parent key name.
	 * 获取完全限定父键名
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        return $this->parent->getQualifiedKeyName();
    }

    /**
     * Get the related model of the relation.
	 * 获取关系的相关模型
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Get the name of the "created at" column.
	 * 获取"创建位置"列的名称
     *
     * @return string
     */
    public function createdAt()
    {
        return $this->parent->getCreatedAtColumn();
    }

    /**
     * Get the name of the "updated at" column.
	 * 获取"更新时间"列的名称
     *
     * @return string
     */
    public function updatedAt()
    {
        return $this->parent->getUpdatedAtColumn();
    }

    /**
     * Get the name of the related model's "updated at" column.
	 * 获取相关模型的"updated at"列的名称
     *
     * @return string
     */
    public function relatedUpdatedAt()
    {
        return $this->related->getUpdatedAtColumn();
    }

    /**
     * Get the name of the "where in" method for eager loading.
	 * 获取即时加载的"where in"方法的名称
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @return string
     */
    protected function whereInMethod(Model $model, $key)
    {
        return $model->getKeyName() === last(explode('.', $key))
                    && in_array($model->getKeyType(), ['int', 'integer'])
                        ? 'whereIntegerInRaw'
                        : 'whereIn';
    }

    /**
     * Prevent polymorphic relationships from being used without model mappings.
	 * 防止在没有模型映射的情况下使用多态关系
     *
     * @param  bool  $requireMorphMap
     * @return void
     */
    public static function requireMorphMap($requireMorphMap = true)
    {
        static::$requireMorphMap = $requireMorphMap;
    }

    /**
     * Determine if polymorphic relationships require explicit model mapping.
	 * 确定多态关系是否需要显式的模型映射
     *
     * @return bool
     */
    public static function requiresMorphMap()
    {
        return static::$requireMorphMap;
    }

    /**
     * Define the morph map for polymorphic relations and require all morphed models to be explicitly mapped.
	 * 为多态关系定义变形映射，并要求显式映射所有变形模型。
     *
     * @param  array  $map
     * @param  bool  $merge
     * @return array
     */
    public static function enforceMorphMap(array $map, $merge = true)
    {
        static::requireMorphMap();

        return static::morphMap($map, $merge);
    }

    /**
     * Set or get the morph map for polymorphic relations.
	 * 设置或获取多态关系的形态映射
     *
     * @param  array|null  $map
     * @param  bool  $merge
     * @return array
     */
    public static function morphMap(array $map = null, $merge = true)
    {
        $map = static::buildMorphMapFromModels($map);

        if (is_array($map)) {
            static::$morphMap = $merge && static::$morphMap
                            ? $map + static::$morphMap : $map;
        }

        return static::$morphMap;
    }

    /**
     * Builds a table-keyed array from model class names.
	 * 根据模型类名构建表键数组
     *
     * @param  string[]|null  $models
     * @return array|null
     */
    protected static function buildMorphMapFromModels(array $models = null)
    {
        if (is_null($models) || Arr::isAssoc($models)) {
            return $models;
        }

        return array_combine(array_map(function ($model) {
            return (new $model)->getTable();
        }, $models), $models);
    }

    /**
     * Get the model associated with a custom polymorphic type.
	 * 获取与自定义多态类型相关联的模型
     *
     * @param  string  $alias
     * @return string|null
     */
    public static function getMorphedModel($alias)
    {
        return static::$morphMap[$alias] ?? null;
    }

    /**
     * Handle dynamic method calls to the relationship.
	 * 处理对关系的动态方法调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->forwardDecoratedCallTo($this->query, $method, $parameters);
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
