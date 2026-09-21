<?php
/**
 * Illuminate，集合，特性，枚举值
 */

namespace Illuminate\Support\Traits;

use CachingIterator;
use Closure;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\HigherOrderCollectionProxy;
use JsonSerializable;
use Symfony\Component\VarDumper\VarDumper;
use Traversable;
use UnexpectedValueException;
use UnitEnum;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @property-read HigherOrderCollectionProxy $average
 * @property-read HigherOrderCollectionProxy $avg
 * @property-read HigherOrderCollectionProxy $contains
 * @property-read HigherOrderCollectionProxy $doesntContain
 * @property-read HigherOrderCollectionProxy $each
 * @property-read HigherOrderCollectionProxy $every
 * @property-read HigherOrderCollectionProxy $filter
 * @property-read HigherOrderCollectionProxy $first
 * @property-read HigherOrderCollectionProxy $flatMap
 * @property-read HigherOrderCollectionProxy $groupBy
 * @property-read HigherOrderCollectionProxy $keyBy
 * @property-read HigherOrderCollectionProxy $map
 * @property-read HigherOrderCollectionProxy $max
 * @property-read HigherOrderCollectionProxy $min
 * @property-read HigherOrderCollectionProxy $partition
 * @property-read HigherOrderCollectionProxy $reject
 * @property-read HigherOrderCollectionProxy $skipUntil
 * @property-read HigherOrderCollectionProxy $skipWhile
 * @property-read HigherOrderCollectionProxy $some
 * @property-read HigherOrderCollectionProxy $sortBy
 * @property-read HigherOrderCollectionProxy $sortByDesc
 * @property-read HigherOrderCollectionProxy $sum
 * @property-read HigherOrderCollectionProxy $takeUntil
 * @property-read HigherOrderCollectionProxy $takeWhile
 * @property-read HigherOrderCollectionProxy $unique
 * @property-read HigherOrderCollectionProxy $unless
 * @property-read HigherOrderCollectionProxy $until
 * @property-read HigherOrderCollectionProxy $when
 */
trait EnumeratesValues
{
    use Conditionable;

    /**
     * Indicates that the object's string representation should be escaped when __toString is invoked.
	 * 指示在调用__toString时应该转义对象的字符串表示形式
     *
     * @var bool
     */
    protected $escapeWhenCastingToString = false;

    /**
     * The methods that can be proxied.
	 * 可以被代理的方法
     *
     * @var array<int, string>
     */
    protected static $proxies = [
        'average',
        'avg',
        'contains',
        'doesntContain',
        'each',
        'every',
        'filter',
        'first',
        'flatMap',
        'groupBy',
        'keyBy',
        'map',
        'max',
        'min',
        'partition',
        'reject',
        'skipUntil',
        'skipWhile',
        'some',
        'sortBy',
        'sortByDesc',
        'sum',
        'takeUntil',
        'takeWhile',
        'unique',
        'unless',
        'until',
        'when',
    ];

    /**
     * Create a new collection instance if the value isn't one already.
	 * 如果该值还没有，则创建一个新的集合实例。
     *
     * @template TMakeKey of array-key
     * @template TMakeValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TMakeKey, TMakeValue>|iterable<TMakeKey, TMakeValue>|null  $items
     * @return static<TMakeKey, TMakeValue>
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Wrap the given value in a collection if applicable.
	 * 如果适用，将给定值包装在集合中。
     *
     * @template TWrapValue
     *
     * @param  iterable<array-key, TWrapValue>|TWrapValue  $value
     * @return static<array-key, TWrapValue>
     */
    public static function wrap($value)
    {
        return $value instanceof Enumerable
            ? new static($value)
            : new static(Arr::wrap($value));
    }

    /**
     * Get the underlying items from the given collection if applicable.
	 * 从给定集合中获取基础项（如果适用）。
     *
     * @template TUnwrapKey of array-key
     * @template TUnwrapValue
     *
     * @param  array<TUnwrapKey, TUnwrapValue>|static<TUnwrapKey, TUnwrapValue>  $value
     * @return array<TUnwrapKey, TUnwrapValue>
     */
    public static function unwrap($value)
    {
        return $value instanceof Enumerable ? $value->all() : $value;
    }

    /**
     * Create a new instance with no items.
	 * 创建一个没有项目的新实例
     *
     * @return static
     */
    public static function empty()
    {
        return new static([]);
    }

    /**
     * Create a new collection by invoking the callback a given amount of times.
	 * 通过调用给定次数的回调来创建新集
     *
     * @template TTimesValue
     *
     * @param  int  $number
     * @param  (callable(int): TTimesValue)|null  $callback
     * @return static<int, TTimesValue>
     */
    public static function times($number, callable $callback = null)
    {
        if ($number < 1) {
            return new static;
        }

        return static::range(1, $number)
            ->unless($callback == null)
            ->map($callback);
    }

    /**
     * Alias for the "avg" method.
	 * "avg"方法的别名
     *
     * @param  (callable(TValue): float|int)|string|null  $callback
     * @return float|int|null
     */
    public function average($callback = null)
    {
        return $this->avg($callback);
    }

    /**
     * Alias for the "contains" method.
	 * contains方法的别名
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function some($key, $operator = null, $value = null)
    {
        return $this->contains(...func_get_args());
    }

    /**
     * Dump the items and end the script.
	 * 转储条目并结束脚本
     *
     * @param  mixed  ...$args
     * @return never
     */
    public function dd(...$args)
    {
        $this->dump(...$args);

        exit(1);
    }

    /**
     * Dump the items.
	 * 扔掉这些东西
     *
     * @return $this
     */
    public function dump()
    {
        (new Collection(func_get_args()))
            ->push($this->all())
            ->each(function ($item) {
                VarDumper::dump($item);
            });

        return $this;
    }

    /**
     * Execute a callback over each item.
	 * 对每个项目执行回调
     *
     * @param  callable(TValue, TKey): mixed  $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Execute a callback over each nested chunk of items.
	 * 对每个嵌套的项块执行回调
     *
     * @param  callable(...mixed): mixed  $callback
     * @return static
     */
    public function eachSpread(callable $callback)
    {
        return $this->each(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Determine if all items pass the given truth test.
	 * 确定是否所有项目都通过给定的真值测试
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function every($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $callback = $this->valueRetriever($key);

            foreach ($this as $k => $v) {
                if (! $callback($v, $k)) {
                    return false;
                }
            }

            return true;
        }

        return $this->every($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get the first item by the given key value pair.
	 * 根据给定的键值对获取第一项
     *
     * @param  callable|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return TValue|null
     */
    public function firstWhere($key, $operator = null, $value = null)
    {
        return $this->first($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get a single key's value from the first matching item in the collection.
	 * 从集合中的第一个匹配项中获取单个键的值
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function value($key, $default = null)
    {
        if ($value = $this->firstWhere($key)) {
            return data_get($value, $key, $default);
        }

        return value($default);
    }

    /**
     * Determine if the collection is not empty.
	 * 确定集合是否为空
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Run a map over each nested chunk of items.
	 * 在每个嵌套的项目块上运行一个映射
     *
     * @template TMapSpreadValue
     *
     * @param  callable(mixed): TMapSpreadValue  $callback
     * @return static<TKey, TMapSpreadValue>
     */
    public function mapSpread(callable $callback)
    {
        return $this->map(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Run a grouping map over the items.
	 * 在项目上运行分组映射
     *
     * The callback should return an associative array with a single key/value pair.
	 * 回调函数应该返回一个具有单个键/值对的关联数组
     *
     * @template TMapToGroupsKey of array-key
     * @template TMapToGroupsValue
     *
     * @param  callable(TValue, TKey): array<TMapToGroupsKey, TMapToGroupsValue>  $callback
     * @return static<TMapToGroupsKey, static<int, TMapToGroupsValue>>
     */
    public function mapToGroups(callable $callback)
    {
        $groups = $this->mapToDictionary($callback);

        return $groups->map([$this, 'make']);
    }

    /**
     * Map a collection and flatten the result by a single level.
	 * 映射一个集合并将结果平铺一个级别
     *
     * @template TFlatMapKey of array-key
     * @template TFlatMapValue
     *
     * @param  callable(TValue, TKey): (\Illuminate\Support\Collection<TFlatMapKey, TFlatMapValue>|array<TFlatMapKey, TFlatMapValue>)  $callback
     * @return static<TFlatMapKey, TFlatMapValue>
     */
    public function flatMap(callable $callback)
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Map the values into a new class.
	 * 将值映射到一个新类
     *
     * @template TMapIntoValue
     *
     * @param  class-string<TMapIntoValue>  $class
     * @return static<TKey, TMapIntoValue>
     */
    public function mapInto($class)
    {
        return $this->map(fn ($value, $key) => new $class($value, $key));
    }

    /**
     * Get the min value of a given key.
	 * 获取给定键的最小值
     *
     * @param  (callable(TValue):mixed)|string|null  $callback
     * @return mixed
     */
    public function min($callback = null)
    {
        $callback = $this->valueRetriever($callback);

        return $this->map(fn ($value) => $callback($value))
            ->filter(fn ($value) => ! is_null($value))
            ->reduce(fn ($result, $value) => is_null($result) || $value < $result ? $value : $result);
    }

    /**
     * Get the max value of a given key.
	 * 获取给定键的最大值
     *
     * @param  (callable(TValue):mixed)|string|null  $callback
     * @return mixed
     */
    public function max($callback = null)
    {
        $callback = $this->valueRetriever($callback);

        return $this->filter(fn ($value) => ! is_null($value))->reduce(function ($result, $item) use ($callback) {
            $value = $callback($item);

            return is_null($result) || $value > $result ? $value : $result;
        });
    }

    /**
     * "Paginate" the collection by slicing it into a smaller collection.
	 * 通过将集合切片为更小的集合来"分页"集合
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return static
     */
    public function forPage($page, $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);

        return $this->slice($offset, $perPage);
    }

    /**
     * Partition the collection into two arrays using the given callback or key.
	 * 使用给定的回调或键将集合划分为两个数组
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  TValue|string|null  $operator
     * @param  TValue|null  $value
     * @return static<int<0, 1>, static<TKey, TValue>>
     */
    public function partition($key, $operator = null, $value = null)
    {
        $passed = [];
        $failed = [];

        $callback = func_num_args() === 1
                ? $this->valueRetriever($key)
                : $this->operatorForWhere(...func_get_args());

        foreach ($this as $key => $item) {
            if ($callback($item, $key)) {
                $passed[$key] = $item;
            } else {
                $failed[$key] = $item;
            }
        }

        return new static([new static($passed), new static($failed)]);
    }

    /**
     * Get the sum of the given values.
	 * 得到给定值的和
     *
     * @param  (callable(TValue): mixed)|string|null  $callback
     * @return mixed
     */
    public function sum($callback = null)
    {
        $callback = is_null($callback)
            ? $this->identity()
            : $this->valueRetriever($callback);

        return $this->reduce(fn ($result, $item) => $result + $callback($item), 0);
    }

    /**
     * Apply the callback if the collection is empty.
	 * 如果集合为空，则应用回调。
     *
     * @template TWhenEmptyReturnType
     *
     * @param  (callable($this): TWhenEmptyReturnType)  $callback
     * @param  (callable($this): TWhenEmptyReturnType)|null  $default
     * @return $this|TWhenEmptyReturnType
     */
    public function whenEmpty(callable $callback, callable $default = null)
    {
        return $this->when($this->isEmpty(), $callback, $default);
    }

    /**
     * Apply the callback if the collection is not empty.
	 * 如果集合不为空，则应用回调。
     *
     * @template TWhenNotEmptyReturnType
     *
     * @param  callable($this): TWhenNotEmptyReturnType  $callback
     * @param  (callable($this): TWhenNotEmptyReturnType)|null  $default
     * @return $this|TWhenNotEmptyReturnType
     */
    public function whenNotEmpty(callable $callback, callable $default = null)
    {
        return $this->when($this->isNotEmpty(), $callback, $default);
    }

    /**
     * Apply the callback unless the collection is empty.
	 * 除非集合为空，否则应用回调。
     *
     * @template TUnlessEmptyReturnType
     *
     * @param  callable($this): TUnlessEmptyReturnType  $callback
     * @param  (callable($this): TUnlessEmptyReturnType)|null  $default
     * @return $this|TUnlessEmptyReturnType
     */
    public function unlessEmpty(callable $callback, callable $default = null)
    {
        return $this->whenNotEmpty($callback, $default);
    }

    /**
     * Apply the callback unless the collection is not empty.
	 * 除非集合不为空，否则应用回调。
     *
     * @template TUnlessNotEmptyReturnType
     *
     * @param  callable($this): TUnlessNotEmptyReturnType  $callback
     * @param  (callable($this): TUnlessNotEmptyReturnType)|null  $default
     * @return $this|TUnlessNotEmptyReturnType
     */
    public function unlessNotEmpty(callable $callback, callable $default = null)
    {
        return $this->whenEmpty($callback, $default);
    }

    /**
     * Filter items by the given key value pair.
	 * 根据给定的键值对筛选项
     *
     * @param  callable|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return static
     */
    public function where($key, $operator = null, $value = null)
    {
        return $this->filter($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Filter items where the value for the given key is null.
	 * 过滤给定键值为空的项
     *
     * @param  string|null  $key
     * @return static
     */
    public function whereNull($key = null)
    {
        return $this->whereStrict($key, null);
    }

    /**
     * Filter items where the value for the given key is not null.
	 * 筛选给定键的值不为空的项
     *
     * @param  string|null  $key
     * @return static
     */
    public function whereNotNull($key = null)
    {
        return $this->where($key, '!==', null);
    }

    /**
     * Filter items by the given key value pair using strict comparison.
	 * 使用严格比较按给定的键值对筛选项
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return static
     */
    public function whereStrict($key, $value)
    {
        return $this->where($key, '===', $value);
    }

    /**
     * Filter items by the given key value pair.
	 * 根据给定的键值对筛选项
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->filter(fn ($item) => in_array(data_get($item, $key), $values, $strict));
    }

    /**
     * Filter items by the given key value pair using strict comparison.
	 * 使用严格比较按给定的键值对筛选项。
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereInStrict($key, $values)
    {
        return $this->whereIn($key, $values, true);
    }

    /**
     * Filter items such that the value of the given key is between the given values.
	 * 筛选项，使给定键的值在给定值之间。
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereBetween($key, $values)
    {
        return $this->where($key, '>=', reset($values))->where($key, '<=', end($values));
    }

    /**
     * Filter items such that the value of the given key is not between the given values.
	 * 筛选项，使给定键的值不在给定值之内。
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereNotBetween($key, $values)
    {
        return $this->filter(
            fn ($item) => data_get($item, $key) < reset($values) || data_get($item, $key) > end($values)
        );
    }

    /**
     * Filter items by the given key value pair.
	 * 根据给定的键值对筛选项
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);

        return $this->reject(fn ($item) => in_array(data_get($item, $key), $values, $strict));
    }

    /**
     * Filter items by the given key value pair using strict comparison.
	 * 使用严格比较按给定的键值对筛选项
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereNotInStrict($key, $values)
    {
        return $this->whereNotIn($key, $values, true);
    }

    /**
     * Filter the items, removing any items that don't match the given type(s).
	 * 筛选项目，删除任何与给定类型不匹配的项目。
     *
     * @template TWhereInstanceOf
     *
     * @param  class-string<TWhereInstanceOf>|array<array-key, class-string<TWhereInstanceOf>>  $type
     * @return static<TKey, TWhereInstanceOf>
     */
    public function whereInstanceOf($type)
    {
        return $this->filter(function ($value) use ($type) {
            if (is_array($type)) {
                foreach ($type as $classType) {
                    if ($value instanceof $classType) {
                        return true;
                    }
                }

                return false;
            }

            return $value instanceof $type;
        });
    }

    /**
     * Pass the collection to the given callback and return the result.
	 * 将集合传递给给定的回调函数并返回结果
     *
     * @template TPipeReturnType
     *
     * @param  callable($this): TPipeReturnType  $callback
     * @return TPipeReturnType
     */
    public function pipe(callable $callback)
    {
        return $callback($this);
    }

    /**
     * Pass the collection into a new class.
	 * 将集合传递到一个新类
     *
     * @param  class-string  $class
     * @return mixed
     */
    public function pipeInto($class)
    {
        return new $class($this);
    }

    /**
     * Pass the collection through a series of callable pipes and return the result.
	 * 将集合通过一系列可调用管道传递并返回结果
     *
     * @param  array<callable>  $callbacks
     * @return mixed
     */
    public function pipeThrough($callbacks)
    {
        return Collection::make($callbacks)->reduce(
            fn ($carry, $callback) => $callback($carry),
            $this,
        );
    }

    /**
     * Reduce the collection to a single value.
	 * 将集合减少为单个值
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param  callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType  $callback
     * @param  TReduceInitial  $initial
     * @return TReduceReturnType
     */
    public function reduce(callable $callback, $initial = null)
    {
        $result = $initial;

        foreach ($this as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * Reduce the collection to multiple aggregate values.
	 * 将集合减少为多个聚合值
     *
     * @param  callable  $callback
     * @param  mixed  ...$initial
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    public function reduceSpread(callable $callback, ...$initial)
    {
        $result = $initial;

        foreach ($this as $key => $value) {
            $result = call_user_func_array($callback, array_merge($result, [$value, $key]));

            if (! is_array($result)) {
                throw new UnexpectedValueException(sprintf(
                    "%s::reduceSpread expects reducer to return an array, but got a '%s' instead.",
                    class_basename(static::class), gettype($result)
                ));
            }
        }

        return $result;
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
	 * 创建一个未通过给定真值测试的所有元素的集合
     *
     * @param  (callable(TValue, TKey): bool)|bool|TValue  $callback
     * @return static
     */
    public function reject($callback = true)
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(function ($value, $key) use ($callback, $useAsCallable) {
            return $useAsCallable
                ? ! $callback($value, $key)
                : $value != $callback;
        });
    }

    /**
     * Pass the collection to the given callback and then return it.
	 * 将集合传递给给定的回调函数，然后返回它。
     *
     * @param  callable($this): mixed  $callback
     * @return $this
     */
    public function tap(callable $callback)
    {
        $callback($this);

        return $this;
    }

    /**
     * Return only unique items from the collection array.
	 * 只返回集合数组中唯一的项
     *
     * @param  (callable(TValue, TKey): mixed)|string|null  $key
     * @param  bool  $strict
     * @return static
     */
    public function unique($key = null, $strict = false)
    {
        $callback = $this->valueRetriever($key);

        $exists = [];

        return $this->reject(function ($item, $key) use ($callback, $strict, &$exists) {
            if (in_array($id = $callback($item, $key), $exists, $strict)) {
                return true;
            }

            $exists[] = $id;
        });
    }

    /**
     * Return only unique items from the collection array using strict comparison.
	 * 使用严格比较只返回集合数组中的唯一项
     *
     * @param  (callable(TValue, TKey): mixed)|string|null  $key
     * @return static
     */
    public function uniqueStrict($key = null)
    {
        return $this->unique($key, true);
    }

    /**
     * Collect the values into a collection.
	 * 将这些值收集到一个集合中
     *
     * @return \Illuminate\Support\Collection<TKey, TValue>
     */
    public function collect()
    {
        return new Collection($this->all());
    }

    /**
     * Get the collection of items as a plain array.
	 * 获取作为普通数组的项集合
     *
     * @return array<TKey, mixed>
     */
    public function toArray()
    {
        return $this->map(fn ($value) => $value instanceof Arrayable ? $value->toArray() : $value)->all();
    }

    /**
     * Convert the object into something JSON serializable.
	 * 将对象转换为JSON可序列化的对
     *
     * @return array<TKey, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->all());
    }

    /**
     * Get the collection of items as JSON.
	 * 以JSON形式获取项目集
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get a CachingIterator instance.
	 * 获取一个CachingIterator实例
     *
     * @param  int  $flags
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
    {
        return new CachingIterator($this->getIterator(), $flags);
    }

    /**
     * Convert the collection to its string representation.
	 * 将集合转换为其字符串表示形式
     *
     * @return string
     */
    public function __toString()
    {
        return $this->escapeWhenCastingToString
                    ? e($this->toJson())
                    : $this->toJson();
    }

    /**
     * Indicate that the model's string representation should be escaped when __toString is invoked.
	 * 表明当__toString被调用时，模型的字符串表示应该被转义。
     *
     * @param  bool  $escape
     * @return $this
     */
    public function escapeWhenCastingToString($escape = true)
    {
        $this->escapeWhenCastingToString = $escape;

        return $this;
    }

    /**
     * Add a method to the list of proxied methods.
	 * 向代理方法列表中添加一个方法
     *
     * @param  string  $method
     * @return void
     */
    public static function proxy($method)
    {
        static::$proxies[] = $method;
    }

    /**
     * Dynamically access collection proxies.
	 * 动态访问集合代理
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($key)
    {
        if (! in_array($key, static::$proxies)) {
            throw new Exception("Property [{$key}] does not exist on this collection instance.");
        }

        return new HigherOrderCollectionProxy($this, $key);
    }

    /**
     * Results array of items from Collection or Arrayable.
	 * 来自Collection或Arrayable的项的结果数组
     *
     * @param  mixed  $items
     * @return array<TKey, TValue>
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Enumerable) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof UnitEnum) {
            return [$items];
        }

        return (array) $items;
    }

    /**
     * Get an operator checker callback.
	 * 获取一个操作符检查器回调
     *
     * @param  callable|string  $key
     * @param  string|null  $operator
     * @param  mixed  $value
     * @return \Closure
     */
    protected function operatorForWhere($key, $operator = null, $value = null)
    {
        if ($this->useAsCallable($key)) {
            return $key;
        }

        if (func_num_args() === 1) {
            $value = true;

            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;

            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            $strings = array_filter([$retrieved, $value], function ($value) {
                return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
            });

            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;
                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
                case '<=>': return $retrieved <=> $value;
            }
        };
    }

    /**
     * Determine if the given value is callable, but not a string.
	 * 确定给定的值是否是可调用的，而不是字符串
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * Get a value retrieving callback.
	 * 获取一个值检索回调
     *
     * @param  callable|string|null  $value
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return fn ($item) => data_get($item, $value);
    }

    /**
     * Make a function to check an item's equality.
	 * 编写一个函数来检查项的相等性
     *
     * @param  mixed  $value
     * @return \Closure(mixed): bool
     */
    protected function equality($value)
    {
        return fn ($item) => $item === $value;
    }

    /**
     * Make a function using another function, by negating its result.
	 * 通过否定另一个函数的结果来创建一个函数
     *
     * @param  \Closure  $callback
     * @return \Closure
     */
    protected function negate(Closure $callback)
    {
        return fn (...$params) => ! $callback(...$params);
    }

    /**
     * Make a function that returns what's passed to it.
	 * 创建一个函数，返回传递给它的内容。
     *
     * @return \Closure(TValue): TValue
     */
    protected function identity()
    {
        return fn ($value) => $value;
    }
}
