<?php
/**
 * Illuminate，数据库，Eloquent，关系，多态
 */

namespace Illuminate\Database\Eloquent\Relations;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;

class MorphTo extends BelongsTo
{
    use InteractsWithDictionary;

    /**
     * The type of the polymorphic relation.
	 * 多态关系的类型
     *
     * @var string
     */
    protected $morphType;

    /**
     * The models whose relations are being eager loaded.
	 * 其关系被热切加载的模型
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $models;

    /**
     * All of the models keyed by ID.
	 * 所有以ID为键的模型
     *
     * @var array
     */
    protected $dictionary = [];

    /**
     * A buffer of dynamic calls to query macros.
	 * 用于动态调用查询宏的缓冲
     *
     * @var array
     */
    protected $macroBuffer = [];

    /**
     * A map of relations to load for each individual morph type.
	 * 要为每个单独的变形类型加载的关系映射
     *
     * @var array
     */
    protected $morphableEagerLoads = [];

    /**
     * A map of relationship counts to load for each individual morph type.
	 * 关系映射计数为每个单独的变形类型加载
     *
     * @var array
     */
    protected $morphableEagerLoadCounts = [];

    /**
     * A map of constraints to apply for each individual morph type.
	 * 应用于每个单独变形类型的约束映射
     *
     * @var array
     */
    protected $morphableConstraints = [];

    /**
     * Create a new morph to relationship instance.
	 * 创建关系实例的新变形
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $type
     * @param  string  $relation
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation)
    {
        $this->morphType = $type;

        parent::__construct($query, $parent, $foreignKey, $ownerKey, $relation);
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
        $this->buildDictionary($this->models = Collection::make($models));
    }

    /**
     * Build a dictionary with the models.
	 * 用这些模型构建一个字典
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    protected function buildDictionary(Collection $models)
    {
        foreach ($models as $model) {
            if ($model->{$this->morphType}) {
                $morphTypeKey = $this->getDictionaryKey($model->{$this->morphType});
                $foreignKeyKey = $this->getDictionaryKey($model->{$this->foreignKey});

                $this->dictionary[$morphTypeKey][$foreignKeyKey][] = $model;
            }
        }
    }

    /**
     * Get the results of the relationship.
	 * 得到关系的结果
     *
     * Called via eager load method of Eloquent query builder.
     *
     * @return mixed
     */
    public function getEager()
    {
        foreach (array_keys($this->dictionary) as $type) {
            $this->matchToMorphParents($type, $this->getResultsByType($type));
        }

        return $this->models;
    }

    /**
     * Get all of the relation results for a type.
	 * 获取一个类型的所有关系结果
     *
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getResultsByType($type)
    {
        $instance = $this->createModelByType($type);

        $ownerKey = $this->ownerKey ?? $instance->getKeyName();

        $query = $this->replayMacros($instance->newQuery())
                            ->mergeConstraintsFrom($this->getQuery())
                            ->with(array_merge(
                                $this->getQuery()->getEagerLoads(),
                                (array) ($this->morphableEagerLoads[get_class($instance)] ?? [])
                            ))
                            ->withCount(
                                (array) ($this->morphableEagerLoadCounts[get_class($instance)] ?? [])
                            );

        if ($callback = ($this->morphableConstraints[get_class($instance)] ?? null)) {
            $callback($query);
        }

        $whereIn = $this->whereInMethod($instance, $ownerKey);

        return $query->{$whereIn}(
            $instance->getTable().'.'.$ownerKey, $this->gatherKeysByType($type, $instance->getKeyType())
        )->get();
    }

    /**
     * Gather all of the foreign keys for a given type.
	 * 收集给定类型的所有外键
     *
     * @param  string  $type
     * @param  string  $keyType
     * @return array
     */
    protected function gatherKeysByType($type, $keyType)
    {
        return $keyType !== 'string'
                    ? array_keys($this->dictionary[$type])
                    : array_map(function ($modelId) {
                        return (string) $modelId;
                    }, array_filter(array_keys($this->dictionary[$type])));
    }

    /**
     * Create a new model instance by type.
	 * 按类型创建一个新的模型实例
     *
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModelByType($type)
    {
        $class = Model::getActualClassNameForMorph($type);

        return tap(new $class, function ($instance) {
            if (! $instance->getConnectionName()) {
                $instance->setConnection($this->getConnection()->getName());
            }
        });
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
        return $models;
    }

    /**
     * Match the results for a given type to their parents.
	 * 将给定类型的结果与其父类型进行匹配
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @return void
     */
    protected function matchToMorphParents($type, Collection $results)
    {
        foreach ($results as $result) {
            $ownerKey = ! is_null($this->ownerKey) ? $this->getDictionaryKey($result->{$this->ownerKey}) : $result->getKey();

            if (isset($this->dictionary[$type][$ownerKey])) {
                foreach ($this->dictionary[$type][$ownerKey] as $model) {
                    $model->setRelation($this->relationName, $result);
                }
            }
        }
    }

    /**
     * Associate the model instance to the given parent.
	 * 将模型实例关联到给定的父实例
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function associate($model)
    {
        if ($model instanceof Model) {
            $foreignKey = $this->ownerKey && $model->{$this->ownerKey}
                            ? $this->ownerKey
                            : $model->getKeyName();
        }

        $this->parent->setAttribute(
            $this->foreignKey, $model instanceof Model ? $model->{$foreignKey} : null
        );

        $this->parent->setAttribute(
            $this->morphType, $model instanceof Model ? $model->getMorphClass() : null
        );

        return $this->parent->setRelation($this->relationName, $model);
    }

    /**
     * Dissociate previously associated model from the given parent.
	 * 将先前关联的模型与给定的父模型分离
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);

        $this->parent->setAttribute($this->morphType, null);

        return $this->parent->setRelation($this->relationName, null);
    }

    /**
     * Touch all of the related models for the relationship.
	 * 触摸关系的所有相关模型
     *
     * @return void
     */
    public function touch()
    {
        if (! is_null($this->child->{$this->foreignKey})) {
            parent::touch();
        }
    }

    /**
     * Make a new related instance for the given model.
	 * 为给定模型创建一个新的相关实例
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function newRelatedInstanceFor(Model $parent)
    {
        return $parent->{$this->getRelationName()}()->getRelated()->newInstance();
    }

    /**
     * Get the foreign key "type" name.
	 * 获取外键"类型"名称
     *
     * @return string
     */
    public function getMorphType()
    {
        return $this->morphType;
    }

    /**
     * Get the dictionary used by the relationship.
	 * 获取关系使用的字典
     *
     * @return array
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * Specify which relations to load for a given morph type.
	 * 指定要为给定的变形类型加载哪些关系
     *
     * @param  array  $with
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function morphWith(array $with)
    {
        $this->morphableEagerLoads = array_merge(
            $this->morphableEagerLoads, $with
        );

        return $this;
    }

    /**
     * Specify which relationship counts to load for a given morph type.
	 * 指定要为给定的变形类型加载哪个关系计数
     *
     * @param  array  $withCount
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function morphWithCount(array $withCount)
    {
        $this->morphableEagerLoadCounts = array_merge(
            $this->morphableEagerLoadCounts, $withCount
        );

        return $this;
    }

    /**
     * Specify constraints on the query for a given morph type.
	 * 为给定的变形类型指定查询约
     *
     * @param  array  $callbacks
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function constrain(array $callbacks)
    {
        $this->morphableConstraints = array_merge(
            $this->morphableConstraints, $callbacks
        );

        return $this;
    }

    /**
     * Replay stored macro calls on the actual related instance.
	 * 在实际相关实例上重播存储的宏调用
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function replayMacros(Builder $query)
    {
        foreach ($this->macroBuffer as $macro) {
            $query->{$macro['method']}(...$macro['parameters']);
        }

        return $query;
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
        try {
            $result = parent::__call($method, $parameters);

            if (in_array($method, ['select', 'selectRaw', 'selectSub', 'addSelect', 'withoutGlobalScopes'])) {
                $this->macroBuffer[] = compact('method', 'parameters');
            }

            return $result;
        }

        // If we tried to call a method that does not exist on the parent Builder instance,
        // we'll assume that we want to call a query macro (e.g. withTrashed) that only
        // exists on related models. We will just store the call and replay it later.
		// 如果我们试图调用父Builder实例上不存在的方法，我们想调用查询宏。
        catch (BadMethodCallException $e) {
            $this->macroBuffer[] = compact('method', 'parameters');

            return $this;
        }
    }
}
