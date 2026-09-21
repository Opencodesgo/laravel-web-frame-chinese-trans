<?php
/**
 * Illuminate，数据库，Eloquent，集合
 */

namespace Illuminate\Database\Eloquent;

use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use LogicException;

/**
 * @template TKey of array-key
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Support\Collection<TKey, TModel>
 */
class Collection extends BaseCollection implements QueueableCollection
{
    /**
     * Find a model in the collection by key.
	 * 按键在集合中查找模型
     *
     * @template TFindDefault
     *
     * @param  mixed  $key
     * @param  TFindDefault  $default
     * @return static<TKey, TModel>|TModel|TFindDefault
     */
    public function find($key, $default = null)
    {
        if ($key instanceof Model) {
            $key = $key->getKey();
        }

        if ($key instanceof Arrayable) {
            $key = $key->toArray();
        }

        if (is_array($key)) {
            if ($this->isEmpty()) {
                return new static;
            }

            return $this->whereIn($this->first()->getKeyName(), $key);
        }

        return Arr::first($this->items, fn ($model) => $model->getKey() == $key, $default);
    }

    /**
     * Load a set of relationships onto the collection.
	 * 将一组关系加载到集合中
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function load($relations)
    {
        if ($this->isNotEmpty()) {
            if (is_string($relations)) {
                $relations = func_get_args();
            }

            $query = $this->first()->newQueryWithoutRelationships()->with($relations);

            $this->items = $query->eagerLoadRelations($this->items);
        }

        return $this;
    }

    /**
     * Load a set of aggregations over relationship's column onto the collection.
	 * 将关系列上的一组聚合加载到集合上
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @param  string  $column
     * @param  string|null  $function
     * @return $this
     */
    public function loadAggregate($relations, $column, $function = null)
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $models = $this->first()->newModelQuery()
            ->whereKey($this->modelKeys())
            ->select($this->first()->getKeyName())
            ->withAggregate($relations, $column, $function)
            ->get()
            ->keyBy($this->first()->getKeyName());

        $attributes = Arr::except(
            array_keys($models->first()->getAttributes()),
            $models->first()->getKeyName()
        );

        $this->each(function ($model) use ($models, $attributes) {
            $extraAttributes = Arr::only($models->get($model->getKey())->getAttributes(), $attributes);

            $model->forceFill($extraAttributes)
                ->syncOriginalAttributes($attributes)
                ->mergeCasts($models->get($model->getKey())->getCasts());
        });

        return $this;
    }

    /**
     * Load a set of relationship counts onto the collection.
	 * 将一组关系计数加载到集合中
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function loadCount($relations)
    {
        return $this->loadAggregate($relations, '*', 'count');
    }

    /**
     * Load a set of relationship's max column values onto the collection.
	 * 将一组关系的最大列值加载到集合中
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMax($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'max');
    }

    /**
     * Load a set of relationship's min column values onto the collection.
	 * 将一组关系的最小列值加载到集合中
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMin($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'min');
    }

    /**
     * Load a set of relationship's column summations onto the collection.
	 * 将一组关系的列求和加载到集合中
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadSum($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'sum');
    }

    /**
     * Load a set of relationship's average column values onto the collection.
	 * 将一组关系的平均列值加载到集合中
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadAvg($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'avg');
    }

    /**
     * Load a set of related existences onto the collection.
	 * 将一组相关存在加载到集合中
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function loadExists($relations)
    {
        return $this->loadAggregate($relations, '*', 'exists');
    }

    /**
     * Load a set of relationships onto the collection if they are not already eager loaded.
	 * 如果一组关系尚未被急切加载，则将它们加载到集合上。
     *
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>|string  $relations
     * @return $this
     */
    public function loadMissing($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        foreach ($relations as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }

            $segments = explode('.', explode(':', $key)[0]);

            if (str_contains($key, ':')) {
                $segments[count($segments) - 1] .= ':'.explode(':', $key)[1];
            }

            $path = [];

            foreach ($segments as $segment) {
                $path[] = [$segment => $segment];
            }

            if (is_callable($value)) {
                $path[count($segments) - 1][end($segments)] = $value;
            }

            $this->loadMissingRelation($this, $path);
        }

        return $this;
    }

    /**
     * Load a relationship path if it is not already eager loaded.
	 * 加载关系路径（如果它还没有被急切加载）
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @param  array  $path
     * @return void
     */
    protected function loadMissingRelation(self $models, array $path)
    {
        $relation = array_shift($path);

        $name = explode(':', key($relation))[0];

        if (is_string(reset($relation))) {
            $relation = reset($relation);
        }

        $models->filter(fn ($model) => ! is_null($model) && ! $model->relationLoaded($name))->load($relation);

        if (empty($path)) {
            return;
        }

        $models = $models->pluck($name)->whereNotNull();

        if ($models->first() instanceof BaseCollection) {
            $models = $models->collapse();
        }

        $this->loadMissingRelation(new static($models), $path);
    }

    /**
     * Load a set of relationships onto the mixed relationship collection.
	 * 将一组关系加载到混合关系集合中
     *
     * @param  string  $relation
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>  $relations
     * @return $this
     */
    public function loadMorph($relation, $relations)
    {
        $this->pluck($relation)
            ->filter()
            ->groupBy(fn ($model) => get_class($model))
            ->each(fn ($models, $className) => static::make($models)->load($relations[$className] ?? []));

        return $this;
    }

    /**
     * Load a set of relationship counts onto the mixed relationship collection.
	 * 将一组关系计数加载到混合关系集合上
     *
     * @param  string  $relation
     * @param  array<array-key, (callable(\Illuminate\Database\Eloquent\Builder): mixed)|string>  $relations
     * @return $this
     */
    public function loadMorphCount($relation, $relations)
    {
        $this->pluck($relation)
            ->filter()
            ->groupBy(fn ($model) => get_class($model))
            ->each(fn ($models, $className) => static::make($models)->loadCount($relations[$className] ?? []));

        return $this;
    }

    /**
     * Determine if a key exists in the collection.
	 * 确定一个键是否存在于集合中
     *
     * @param  (callable(TModel, TKey): bool)|TModel|string|int  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() > 1 || $this->useAsCallable($key)) {
            return parent::contains(...func_get_args());
        }

        if ($key instanceof Model) {
            return parent::contains(fn ($model) => $model->is($key));
        }

        return parent::contains(fn ($model) => $model->getKey() == $key);
    }

    /**
     * Get the array of primary keys.
	 * 获取主键数组
     *
     * @return array<int, array-key>
     */
    public function modelKeys()
    {
        return array_map(fn ($model) => $model->getKey(), $this->items);
    }

    /**
     * Merge the collection with the given items.
	 * 将集合与给定的项合并
     *
     * @param  iterable<array-key, TModel>  $items
     * @return static
     */
    public function merge($items)
    {
        $dictionary = $this->getDictionary();

        foreach ($items as $item) {
            $dictionary[$item->getKey()] = $item;
        }

        return new static(array_values($dictionary));
    }

    /**
     * Run a map over each of the items.
	 * 在每个项目上运行一张地图
     *
     * @template TMapValue
     *
     * @param  callable(TModel, TKey): TMapValue  $callback
     * @return \Illuminate\Support\Collection<TKey, TMapValue>|static<TKey, TMapValue>
     */
    public function map(callable $callback)
    {
        $result = parent::map($callback);

        return $result->contains(fn ($item) => ! $item instanceof Model) ? $result->toBase() : $result;
    }

    /**
     * Run an associative map over each of the items.
	 * 在每个项目上运行一个关联映射
     *
     * The callback should return an associative array with a single key / value pair.
     *
     * @template TMapWithKeysKey of array-key
     * @template TMapWithKeysValue
     *
     * @param  callable(TModel, TKey): array<TMapWithKeysKey, TMapWithKeysValue>  $callback
     * @return \Illuminate\Support\Collection<TMapWithKeysKey, TMapWithKeysValue>|static<TMapWithKeysKey, TMapWithKeysValue>
     */
    public function mapWithKeys(callable $callback)
    {
        $result = parent::mapWithKeys($callback);

        return $result->contains(fn ($item) => ! $item instanceof Model) ? $result->toBase() : $result;
    }

    /**
     * Reload a fresh model instance from the database for all the entities.
	 * 为所有实体从数据库中重新加载一个新的模型实例
     *
     * @param  array<array-key, string>|string  $with
     * @return static
     */
    public function fresh($with = [])
    {
        if ($this->isEmpty()) {
            return new static;
        }

        $model = $this->first();

        $freshModels = $model->newQueryWithoutScopes()
            ->with(is_string($with) ? func_get_args() : $with)
            ->whereIn($model->getKeyName(), $this->modelKeys())
            ->get()
            ->getDictionary();

        return $this->filter(fn ($model) => $model->exists && isset($freshModels[$model->getKey()]))
            ->map(fn ($model) => $freshModels[$model->getKey()]);
    }

    /**
     * Diff the collection with the given items.
	 * 将集合与给定的项进行比较
     *
     * @param  iterable<array-key, TModel>  $items
     * @return static
     */
    public function diff($items)
    {
        $diff = new static;

        $dictionary = $this->getDictionary($items);

        foreach ($this->items as $item) {
            if (! isset($dictionary[$item->getKey()])) {
                $diff->add($item);
            }
        }

        return $diff;
    }

    /**
     * Intersect the collection with the given items.
	 * 将集合与给定的项目相交
     *
     * @param  iterable<array-key, TModel>  $items
     * @return static
     */
    public function intersect($items)
    {
        $intersect = new static;

        if (empty($items)) {
            return $intersect;
        }

        $dictionary = $this->getDictionary($items);

        foreach ($this->items as $item) {
            if (isset($dictionary[$item->getKey()])) {
                $intersect->add($item);
            }
        }

        return $intersect;
    }

    /**
     * Return only unique items from the collection.
	 * 只返回集合中唯一的项
     *
     * @param  (callable(TModel, TKey): mixed)|string|null  $key
     * @param  bool  $strict
     * @return static<int, TModel>
     */
    public function unique($key = null, $strict = false)
    {
        if (! is_null($key)) {
            return parent::unique($key, $strict);
        }

        return new static(array_values($this->getDictionary()));
    }

    /**
     * Returns only the models from the collection with the specified keys.
	 * 仅返回集合中具有指定键的模型
     *
     * @param  array<array-key, mixed>|null  $keys
     * @return static<int, TModel>
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        $dictionary = Arr::only($this->getDictionary(), $keys);

        return new static(array_values($dictionary));
    }

    /**
     * Returns all models in the collection except the models with specified keys.
	 * 返回集合中除具有指定键的模型外的所有模型
     *
     * @param  array<array-key, mixed>|null  $keys
     * @return static<int, TModel>
     */
    public function except($keys)
    {
        $dictionary = Arr::except($this->getDictionary(), $keys);

        return new static(array_values($dictionary));
    }

    /**
     * Make the given, typically visible, attributes hidden across the entire collection.
	 * 将给定的（通常是可见的）属性隐藏在整个集合中
     *
     * @param  array<array-key, string>|string  $attributes
     * @return $this
     */
    public function makeHidden($attributes)
    {
        return $this->each->makeHidden($attributes);
    }

    /**
     * Make the given, typically hidden, attributes visible across the entire collection.
	 * 使给定的（通常是隐藏的）属性在整个集合中可见
     *
     * @param  array<array-key, string>|string  $attributes
     * @return $this
     */
    public function makeVisible($attributes)
    {
        return $this->each->makeVisible($attributes);
    }

    /**
     * Set the visible attributes across the entire collection.
	 * 在整个集合中设置可见属性
     *
     * @param  array<int, string>  $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        return $this->each->setVisible($visible);
    }

    /**
     * Set the hidden attributes across the entire collection.
	 * 在整个集合中设置隐藏属性
     *
     * @param  array<int, string>  $hidden
     * @return $this
     */
    public function setHidden($hidden)
    {
        return $this->each->setHidden($hidden);
    }

    /**
     * Append an attribute across the entire collection.
	 * 在整个集合中追加一个属性
     *
     * @param  array<array-key, string>|string  $attributes
     * @return $this
     */
    public function append($attributes)
    {
        return $this->each->append($attributes);
    }

    /**
     * Get a dictionary keyed by primary keys.
	 * 获取以主键为键的字典
     *
     * @param  iterable<array-key, TModel>|null  $items
     * @return array<array-key, TModel>
     */
    public function getDictionary($items = null)
    {
        $items = is_null($items) ? $this->items : $items;

        $dictionary = [];

        foreach ($items as $value) {
            $dictionary[$value->getKey()] = $value;
        }

        return $dictionary;
    }

    /**
     * The following methods are intercepted to always return base collections.
	 * 截取以下方法以始终返回基集合
     */

    /**
     * Count the number of items in the collection by a field or using a callback.
	 * 按字段或使用回调计算集合中的项数
     *
     * @param  (callable(TModel, TKey): array-key)|string|null  $countBy
     * @return \Illuminate\Support\Collection<array-key, int>
     */
    public function countBy($countBy = null)
    {
        return $this->toBase()->countBy($countBy);
    }

    /**
     * Collapse the collection of items into a single array.
	 * 将项目集合折叠成单个数组
     *
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function collapse()
    {
        return $this->toBase()->collapse();
    }

    /**
     * Get a flattened array of the items in the collection.
	 * 获取集合中项的扁平数组
     *
     * @param  int  $depth
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    public function flatten($depth = INF)
    {
        return $this->toBase()->flatten($depth);
    }

    /**
     * Flip the items in the collection.
	 * 翻转集合中的项目
     *
     * @return \Illuminate\Support\Collection<TModel, TKey>
     */
    public function flip()
    {
        return $this->toBase()->flip();
    }

    /**
     * Get the keys of the collection items.
	 * 获得收集项目的密钥
     *
     * @return \Illuminate\Support\Collection<int, TKey>
     */
    public function keys()
    {
        return $this->toBase()->keys();
    }

    /**
     * Pad collection to the specified length with a value.
	 * 使用值将集合垫到指定的长度
     *
     * @template TPadValue
     *
     * @param  int  $size
     * @param  TPadValue  $value
     * @return \Illuminate\Support\Collection<int, TModel|TPadValue>
     */
    public function pad($size, $value)
    {
        return $this->toBase()->pad($size, $value);
    }

    /**
     * Get an array with the values of a given key.
	 * 获取具有给定键值的数组
     *
     * @param  string|array<array-key, string>  $value
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection<array-key, mixed>
     */
    public function pluck($value, $key = null)
    {
        return $this->toBase()->pluck($value, $key);
    }

    /**
     * Zip the collection together with one or more arrays.
	 * 将集合与一个或多个数组压缩在一起
     *
     * @template TZipValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TZipValue>|iterable<array-key, TZipValue>  ...$items
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, TModel|TZipValue>>
     */
    public function zip($items)
    {
        return $this->toBase()->zip(...func_get_args());
    }

    /**
     * Get the comparison function to detect duplicates.
	 * 获取比较函数以检测重复项
     *
     * @param  bool  $strict
     * @return callable(TModel, TModel): bool
     */
    protected function duplicateComparator($strict)
    {
        return fn ($a, $b) => $a->is($b);
    }

    /**
     * Get the type of the entities being queued.
	 * 获取正在排队的实体的类型
     *
     * @return string|null
     *
     * @throws \LogicException
     */
    public function getQueueableClass()
    {
        if ($this->isEmpty()) {
            return;
        }

        $class = $this->getQueueableModelClass($this->first());

        $this->each(function ($model) use ($class) {
            if ($this->getQueueableModelClass($model) !== $class) {
                throw new LogicException('Queueing collections with multiple model types is not supported.');
            }
        });

        return $class;
    }

    /**
     * Get the queueable class name for the given model.
	 * 获取给定模型的可排队类名
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected function getQueueableModelClass($model)
    {
        return method_exists($model, 'getQueueableClassName')
                ? $model->getQueueableClassName()
                : get_class($model);
    }

    /**
     * Get the identifiers for all of the entities.
	 * 获取所有实体的标识符
     *
     * @return array<int, mixed>
     */
    public function getQueueableIds()
    {
        if ($this->isEmpty()) {
            return [];
        }

        return $this->first() instanceof QueueableEntity
                    ? $this->map->getQueueableId()->all()
                    : $this->modelKeys();
    }

    /**
     * Get the relationships of the entities being queued.
	 * 获取正在排队的实体之间的关系
     *
     * @return array<int, string>
     */
    public function getQueueableRelations()
    {
        if ($this->isEmpty()) {
            return [];
        }

        $relations = $this->map->getQueueableRelations()->all();

        if (count($relations) === 0 || $relations === [[]]) {
            return [];
        } elseif (count($relations) === 1) {
            return reset($relations);
        } else {
            return array_intersect(...array_values($relations));
        }
    }

    /**
     * Get the connection of the entities being queued.
	 * 获取正在排队的实体的连接
     *
     * @return string|null
     *
     * @throws \LogicException
     */
    public function getQueueableConnection()
    {
        if ($this->isEmpty()) {
            return;
        }

        $connection = $this->first()->getConnectionName();

        $this->each(function ($model) use ($connection) {
            if ($model->getConnectionName() !== $connection) {
                throw new LogicException('Queueing collections with multiple model connections is not supported.');
            }
        });

        return $connection;
    }

    /**
     * Get the Eloquent query builder from the collection.
	 * 从集合中获取Eloquent查询生成器
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \LogicException
     */
    public function toQuery()
    {
        $model = $this->first();

        if (! $model) {
            throw new LogicException('Unable to create query for empty collection.');
        }

        $class = get_class($model);

        if ($this->filter(fn ($model) => ! $model instanceof $class)->isNotEmpty()) {
            throw new LogicException('Unable to create query for collection with mixed types.');
        }

        return $model->newModelQuery()->whereKey($this->modelKeys());
    }
}
