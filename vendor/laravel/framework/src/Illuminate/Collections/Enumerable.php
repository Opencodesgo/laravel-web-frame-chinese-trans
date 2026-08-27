<?php
/**
 * Illuminate，集合，可列举的
 */

namespace Illuminate\Support;

use CachingIterator;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends \Illuminate\Contracts\Support\Arrayable<TKey, TValue>
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface Enumerable extends Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
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
    public static function make($items = []);

    /**
     * Create a new instance by invoking the callback a given amount of times.
	 * 通过调用给定次数的回调来创建一个新实例
     *
     * @param  int  $number
     * @param  callable|null  $callback
     * @return static
     */
    public static function times($number, callable $callback = null);

    /**
     * Create a collection with the given range.
	 * 创建具有给定范围的集合
     *
     * @param  int  $from
     * @param  int  $to
     * @return static
     */
    public static function range($from, $to);

    /**
     * Wrap the given value in a collection if applicable.
	 * 如果适用，将给定值包装在集合中。
     *
     * @template TWrapValue
     *
     * @param  iterable<array-key, TWrapValue>|TWrapValue  $value
     * @return static<array-key, TWrapValue>
     */
    public static function wrap($value);

    /**
     * Get the underlying items from the given collection if applicable.
	 * 从给定集合中获取基础项（如果适用）
     *
     * @template TUnwrapKey of array-key
     * @template TUnwrapValue
     *
     * @param  array<TUnwrapKey, TUnwrapValue>|static<TUnwrapKey, TUnwrapValue>  $value
     * @return array<TUnwrapKey, TUnwrapValue>
     */
    public static function unwrap($value);

    /**
     * Create a new instance with no items.
	 * 创建一个没有项目的新实例
     *
     * @return static
     */
    public static function empty();

    /**
     * Get all items in the enumerable.
	 * 获取枚举中的所有项
     *
     * @return array
     */
    public function all();

    /**
     * Alias for the "avg" method.
	 * "avg"方法的别名
     *
     * @param  (callable(TValue): float|int)|string|null  $callback
     * @return float|int|null
     */
    public function average($callback = null);

    /**
     * Get the median of a given key.
	 * 求给定键的中值
     *
     * @param  string|array<array-key, string>|null  $key
     * @return float|int|null
     */
    public function median($key = null);

    /**
     * Get the mode of a given key.
	 * 获取给定键的模式
     *
     * @param  string|array<array-key, string>|null  $key
     * @return array<int, float|int>|null
     */
    public function mode($key = null);

    /**
     * Collapse the items into a single enumerable.
	 * 将这些项折叠成单个枚举
     *
     * @return static<int, mixed>
     */
    public function collapse();

    /**
     * Alias for the "contains" method.
	 * contains方法的别名
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function some($key, $operator = null, $value = null);

    /**
     * Determine if an item exists, using strict comparison.
	 * 使用严格比较确定项是否存在
     *
     * @param  (callable(TValue): bool)|TValue|array-key  $key
     * @param  TValue|null  $value
     * @return bool
     */
    public function containsStrict($key, $value = null);

    /**
     * Get the average value of a given key.
	 * 获取给定键的平均值
     *
     * @param  (callable(TValue): float|int)|string|null  $callback
     * @return float|int|null
     */
    public function avg($callback = null);

    /**
     * Determine if an item exists in the enumerable.
	 * 确定枚举中是否存在项
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function contains($key, $operator = null, $value = null);

    /**
     * Determine if an item is not contained in the collection.
	 * 确定集合中是否不包含某项
     *
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function doesntContain($key, $operator = null, $value = null);

    /**
     * Cross join with the given lists, returning all possible permutations.
	 * 与给定列表交叉连接，返回所有可能的排列。
     *
     * @template TCrossJoinKey
     * @template TCrossJoinValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TCrossJoinKey, TCrossJoinValue>|iterable<TCrossJoinKey, TCrossJoinValue>  ...$lists
     * @return static<int, array<int, TValue|TCrossJoinValue>>
     */
    public function crossJoin(...$lists);

    /**
     * Dump the collection and end the script.
	 * 转储集合并结束脚本
     *
     * @param  mixed  ...$args
     * @return never
     */
    public function dd(...$args);

    /**
     * Dump the collection.
	 * 转储集合
     *
     * @return $this
     */
    public function dump();

    /**
     * Get the items that are not present in the given items.
	 * 获取在给定项中不存在的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
     * @return static
     */
    public function diff($items);

    /**
     * Get the items that are not present in the given items, using the callback.
	 * 使用回调获取给定项中不存在的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
     * @param  callable(TValue, TValue): int  $callback
     * @return static
     */
    public function diffUsing($items, callable $callback);

    /**
     * Get the items whose keys and values are not present in the given items.
	 * 获取其键和值未出现在给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function diffAssoc($items);

    /**
     * Get the items whose keys and values are not present in the given items, using the callback.
	 * 使用回调获取其键和值未出现在给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @param  callable(TKey, TKey): int  $callback
     * @return static
     */
    public function diffAssocUsing($items, callable $callback);

    /**
     * Get the items whose keys are not present in the given items.
	 * 获取键不存在于给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function diffKeys($items);

    /**
     * Get the items whose keys are not present in the given items, using the callback.
	 * 使用回调获取键不在给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @param  callable(TKey, TKey): int  $callback
     * @return static
     */
    public function diffKeysUsing($items, callable $callback);

    /**
     * Retrieve duplicate items.
	 * 检索重复项
     *
     * @param  (callable(TValue): bool)|string|null  $callback
     * @param  bool  $strict
     * @return static
     */
    public function duplicates($callback = null, $strict = false);

    /**
     * Retrieve duplicate items using strict comparison.
	 * 使用严格比较检索重复项
     *
     * @param  (callable(TValue): bool)|string|null  $callback
     * @return static
     */
    public function duplicatesStrict($callback = null);

    /**
     * Execute a callback over each item.
	 * 对每个项目执行回调
     *
     * @param  callable(TValue, TKey): mixed  $callback
     * @return $this
     */
    public function each(callable $callback);

    /**
     * Execute a callback over each nested chunk of items.
	 * 对每个嵌套的项块执行回调
     *
     * @param  callable  $callback
     * @return static
     */
    public function eachSpread(callable $callback);

    /**
     * Determine if all items pass the given truth test.
	 * 确定是否所有项目都通过给定的真值测试
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function every($key, $operator = null, $value = null);

    /**
     * Get all items except for those with the specified keys.
	 * 获取除具有指定键的项之外的所有项
     *
     * @param  \Illuminate\Support\Enumerable<array-key, TKey>|array<array-key, TKey>  $keys
     * @return static
     */
    public function except($keys);

    /**
     * Run a filter over each of the items.
	 * 对每个项目运行一个过滤器
     *
     * @param  (callable(TValue): bool)|null  $callback
     * @return static
     */
    public function filter(callable $callback = null);

    /**
     * Apply the callback if the given "value" is (or resolves to) truthy.
	 * 如果给定的"值"为真（或解析为真），则应用回调。
     *
     * @template TWhenReturnType as null
     *
     * @param  bool  $value
     * @param  (callable($this): TWhenReturnType)|null  $callback
     * @param  (callable($this): TWhenReturnType)|null  $default
     * @return $this|TWhenReturnType
     */
    public function when($value, callable $callback = null, callable $default = null);

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
    public function whenEmpty(callable $callback, callable $default = null);

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
    public function whenNotEmpty(callable $callback, callable $default = null);

    /**
     * Apply the callback if the given "value" is (or resolves to) truthy.
	 * 如果给定的"值"为真（或解析为真），则应用回调。
     *
     * @template TUnlessReturnType
     *
     * @param  bool  $value
     * @param  (callable($this): TUnlessReturnType)  $callback
     * @param  (callable($this): TUnlessReturnType)|null  $default
     * @return $this|TUnlessReturnType
     */
    public function unless($value, callable $callback, callable $default = null);

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
    public function unlessEmpty(callable $callback, callable $default = null);

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
    public function unlessNotEmpty(callable $callback, callable $default = null);

    /**
     * Filter items by the given key value pair.
	 * 根据给定的键值对筛选项
     *
     * @param  string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return static
     */
    public function where($key, $operator = null, $value = null);

    /**
     * Filter items where the value for the given key is null.
	 * 过滤给定键值为空的项
     *
     * @param  string|null  $key
     * @return static
     */
    public function whereNull($key = null);

    /**
     * Filter items where the value for the given key is not null.
	 * 筛选给定键的值不为空的项
     *
     * @param  string|null  $key
     * @return static
     */
    public function whereNotNull($key = null);

    /**
     * Filter items by the given key value pair using strict comparison.
	 * 使用严格比较按给定的键值对筛选项
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return static
     */
    public function whereStrict($key, $value);

    /**
     * Filter items by the given key value pair.
	 * 根据给定的键值对筛选项
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereIn($key, $values, $strict = false);

    /**
     * Filter items by the given key value pair using strict comparison.
	 * 使用严格比较按给定的键值对筛选项
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereInStrict($key, $values);

    /**
     * Filter items such that the value of the given key is between the given values.
	 * 筛选项，使给定键的值在给定值之间。
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereBetween($key, $values);

    /**
     * Filter items such that the value of the given key is not between the given values.
	 * 筛选项，使给定键的值不在给定值之间。
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereNotBetween($key, $values);

    /**
     * Filter items by the given key value pair.
	 * 根据给定的键值对筛选项
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false);

    /**
     * Filter items by the given key value pair using strict comparison.
	 * 使用严格比较按给定的键值对筛选项
     *
     * @param  string  $key
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $values
     * @return static
     */
    public function whereNotInStrict($key, $values);

    /**
     * Filter the items, removing any items that don't match the given type(s).
	 * 筛选项目，删除任何与给定类型不匹配的项目。
     *
     * @template TWhereInstanceOf
     *
     * @param  class-string<TWhereInstanceOf>|array<array-key, class-string<TWhereInstanceOf>>  $type
     * @return static<TKey, TWhereInstanceOf>
     */
    public function whereInstanceOf($type);

    /**
     * Get the first item from the enumerable passing the given truth test.
	 * 从通过给定真值测试的枚举中获取第一项
     *
     * @template TFirstDefault
     *
     * @param  (callable(TValue,TKey): bool)|null  $callback
     * @param  TFirstDefault|(\Closure(): TFirstDefault)  $default
     * @return TValue|TFirstDefault
     */
    public function first(callable $callback = null, $default = null);

    /**
     * Get the first item by the given key value pair.
	 * 根据给定的键值对获取第一项
     *
     * @param  string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return TValue|null
     */
    public function firstWhere($key, $operator = null, $value = null);

    /**
     * Get a flattened array of the items in the collection.
	 * 获取集合中项的扁平数组
     *
     * @param  int  $depth
     * @return static
     */
    public function flatten($depth = INF);

    /**
     * Flip the values with their keys.
	 * 用键翻转值
     *
     * @return static<TValue, TKey>
     */
    public function flip();

    /**
     * Get an item from the collection by key.
	 * 按键从集合中获取项
     *
     * @template TGetDefault
     *
     * @param  TKey  $key
     * @param  TGetDefault|(\Closure(): TGetDefault)  $default
     * @return TValue|TGetDefault
     */
    public function get($key, $default = null);

    /**
     * Group an associative array by a field or using a callback.
	 * 按字段或使用回调对关联数组进行分组
     *
     * @param  (callable(TValue, TKey): array-key)|array|string  $groupBy
     * @param  bool  $preserveKeys
     * @return static<array-key, static<array-key, TValue>>
     */
    public function groupBy($groupBy, $preserveKeys = false);

    /**
     * Key an associative array by a field or using a callback.
	 * 通过字段或使用回调为关联数组设置键
     *
     * @param  (callable(TValue, TKey): array-key)|array|string  $keyBy
     * @return static<array-key, TValue>
     */
    public function keyBy($keyBy);

    /**
     * Determine if an item exists in the collection by key.
	 * 根据键确定集合中是否存在项
     *
     * @param  TKey|array<array-key, TKey>  $key
     * @return bool
     */
    public function has($key);

    /**
     * Determine if any of the keys exist in the collection.
	 * 确定集合中是否存在任何键
     *
     * @param  mixed  $key
     * @return bool
     */
    public function hasAny($key);

    /**
     * Concatenate values of a given key as a string.
	 * 将给定键的值连接为字符串
     *
     * @param  string  $value
     * @param  string|null  $glue
     * @return string
     */
    public function implode($value, $glue = null);

    /**
     * Intersect the collection with the given items.
	 * 将集合与给定的项目相交
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function intersect($items);

    /**
     * Intersect the collection with the given items by key.
	 * 通过键将集合与给定的项目相交
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function intersectByKeys($items);

    /**
     * Determine if the collection is empty or not.
	 * 确定集合是否为空
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Determine if the collection is not empty.
	 * 确定集合是否为空
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Determine if the collection contains a single item.
	 * 确定集合是否包含单个项
     *
     * @return bool
     */
    public function containsOneItem();

    /**
     * Join all items from the collection using a string. The final items can use a separate glue string.
	 * 使用字符串连接集合中的所有项。最后的项目可以使用一个单独的胶水线。
     *
     * @param  string  $glue
     * @param  string  $finalGlue
     * @return string
     */
    public function join($glue, $finalGlue = '');

    /**
     * Get the keys of the collection items.
	 * 获得收集项目的钥匙
     *
     * @return static<int, TKey>
     */
    public function keys();

    /**
     * Get the last item from the collection.
	 * 从集合中获取最后一项
     *
     * @template TLastDefault
     *
     * @param  (callable(TValue, TKey): bool)|null  $callback
     * @param  TLastDefault|(\Closure(): TLastDefault)  $default
     * @return TValue|TLastDefault
     */
    public function last(callable $callback = null, $default = null);

    /**
     * Run a map over each of the items.
	 * 在每个项目上运行一张地图
     *
     * @template TMapValue
     *
     * @param  callable(TValue, TKey): TMapValue  $callback
     * @return static<TKey, TMapValue>
     */
    public function map(callable $callback);

    /**
     * Run a map over each nested chunk of items.
	 * 在每个嵌套的项目块上运行一个映射
     *
     * @param  callable  $callback
     * @return static
     */
    public function mapSpread(callable $callback);

    /**
     * Run a dictionary map over the items.
	 * 在条目上运行字典映射
     *
     * The callback should return an associative array with a single key/value pair.
	 * 回调函数应该返回一个具有单个键/值对的关联数组
     *
     * @template TMapToDictionaryKey of array-key
     * @template TMapToDictionaryValue
     *
     * @param  callable(TValue, TKey): array<TMapToDictionaryKey, TMapToDictionaryValue>  $callback
     * @return static<TMapToDictionaryKey, array<int, TMapToDictionaryValue>>
     */
    public function mapToDictionary(callable $callback);

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
    public function mapToGroups(callable $callback);

    /**
     * Run an associative map over each of the items.
	 * 在每个项目上运行一个关联映射
     *
     * The callback should return an associative array with a single key/value pair.
	 * 回调函数应该返回一个具有单个键/值对的关联数组
     *
     * @template TMapWithKeysKey of array-key
     * @template TMapWithKeysValue
     *
     * @param  callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue>  $callback
     * @return static<TMapWithKeysKey, TMapWithKeysValue>
     */
    public function mapWithKeys(callable $callback);

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
    public function flatMap(callable $callback);

    /**
     * Map the values into a new class.
	 * 将值映射到一个新类
     *
     * @template TMapIntoValue
     *
     * @param  class-string<TMapIntoValue>  $class
     * @return static<TKey, TMapIntoValue>
     */
    public function mapInto($class);

    /**
     * Merge the collection with the given items.
	 * 将集合与给定的项合并
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function merge($items);

    /**
     * Recursively merge the collection with the given items.
	 * 递归地将集合与给定项合并
     *
     * @template TMergeRecursiveValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TMergeRecursiveValue>|iterable<TKey, TMergeRecursiveValue>  $items
     * @return static<TKey, TValue|TMergeRecursiveValue>
     */
    public function mergeRecursive($items);

    /**
     * Create a collection by using this collection for keys and another for its values.
	 * 创建一个集合，将这个集合用于键，另一个用于它的值。
     *
     * @template TCombineValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TCombineValue>|iterable<array-key, TCombineValue>  $values
     * @return static<TValue, TCombineValue>
     */
    public function combine($values);

    /**
     * Union the collection with the given items.
	 * 将集合与给定项联合
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function union($items);

    /**
     * Get the min value of a given key.
	 * 获取给定键的最小值
     *
     * @param  (callable(TValue):mixed)|string|null  $callback
     * @return mixed
     */
    public function min($callback = null);

    /**
     * Get the max value of a given key.
	 * 获取给定键的最大值
     *
     * @param  (callable(TValue):mixed)|string|null  $callback
     * @return mixed
     */
    public function max($callback = null);

    /**
     * Create a new collection consisting of every n-th element.
	 * 创建一个包含每n个元素的新集合
     *
     * @param  int  $step
     * @param  int  $offset
     * @return static
     */
    public function nth($step, $offset = 0);

    /**
     * Get the items with the specified keys.
	 * 获取具有指定键的项
     *
     * @param  \Illuminate\Support\Enumerable<array-key, TKey>|array<array-key, TKey>|string  $keys
     * @return static
     */
    public function only($keys);

    /**
     * "Paginate" the collection by slicing it into a smaller collection.
	 * 通过将集合切片为更小的集合来"分页"集合
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return static
     */
    public function forPage($page, $perPage);

    /**
     * Partition the collection into two arrays using the given callback or key.
	 * 使用给定的回调或键将集合划分为两个数组
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return static<int<0, 1>, static<TKey, TValue>>
     */
    public function partition($key, $operator = null, $value = null);

    /**
     * Push all of the given items onto the collection.
	 * 将所有给定的项推入集合
     *
     * @param  iterable<array-key, TValue>  $source
     * @return static
     */
    public function concat($source);

    /**
     * Get one or a specified number of items randomly from the collection.
	 * 从集合中随机获取一个或指定数量的项
     *
     * @param  int|null  $number
     * @return static<int, TValue>|TValue
     *
     * @throws \InvalidArgumentException
     */
    public function random($number = null);

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
    public function reduce(callable $callback, $initial = null);

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
    public function reduceSpread(callable $callback, ...$initial);

    /**
     * Replace the collection items with the given items.
	 * 用给定的项替换集合项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function replace($items);

    /**
     * Recursively replace the collection items with the given items.
	 * 递归地将集合项替换为给定项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function replaceRecursive($items);

    /**
     * Reverse items order.
	 * 颠倒项目顺序
     *
     * @return static
     */
    public function reverse();

    /**
     * Search the collection for a given value and return the corresponding key if successful.
	 * 在集合中搜索给定的值，如果成功则返回相应的键。
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @param  bool  $strict
     * @return TKey|bool
     */
    public function search($value, $strict = false);

    /**
     * Shuffle the items in the collection.
	 * 对集合中的项进行洗牌
     *
     * @param  int|null  $seed
     * @return static
     */
    public function shuffle($seed = null);

    /**
     * Create chunks representing a "sliding window" view of the items in the collection.
	 * 创建块，表示集合中项的"滑动窗口"视图。
     *
     * @param  int  $size
     * @param  int  $step
     * @return static<int, static>
     */
    public function sliding($size = 2, $step = 1);

    /**
     * Skip the first {$count} items.
	 * 跳过第一个{$count}项
     *
     * @param  int  $count
     * @return static
     */
    public function skip($count);

    /**
     * Skip items in the collection until the given condition is met.
	 * 跳过集合中的项，直到满足给定的条件。
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function skipUntil($value);

    /**
     * Skip items in the collection while the given condition is met.
	 * 在满足给定条件时跳过集合中的项
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function skipWhile($value);

    /**
     * Get a slice of items from the enumerable.
	 * 从可枚举对象中获取项目的切片
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @return static
     */
    public function slice($offset, $length = null);

    /**
     * Split a collection into a certain number of groups.
	 * 将一个集合分成一定数量的组
     *
     * @param  int  $numberOfGroups
     * @return static<int, static>
     */
    public function split($numberOfGroups);

    /**
     * Get the first item in the collection, but only if exactly one item exists. Otherwise, throw an exception.
	 * 获取集合中的第一项，但仅当存在一项时。否则，抛出异常。
     *
     * @param  (callable(TValue, TKey): bool)|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return TValue
     *
     * @throws \Illuminate\Support\ItemNotFoundException
     * @throws \Illuminate\Support\MultipleItemsFoundException
     */
    public function sole($key = null, $operator = null, $value = null);

    /**
     * Get the first item in the collection but throw an exception if no matching items exist.
	 * 获取集合中的第一项，但如果不存在匹配项则抛出异常。
     *
     * @param  (callable(TValue, TKey): bool)|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return TValue
     *
     * @throws \Illuminate\Support\ItemNotFoundException
     */
    public function firstOrFail($key = null, $operator = null, $value = null);

    /**
     * Chunk the collection into chunks of the given size.
	 * 将集合分成给定大小的块
     *
     * @param  int  $size
     * @return static<int, static>
     */
    public function chunk($size);

    /**
     * Chunk the collection into chunks with a callback.
	 * 使用回调将集合分成块
     *
     * @param  callable(TValue, TKey, static<int, TValue>): bool  $callback
     * @return static<int, static<int, TValue>>
     */
    public function chunkWhile(callable $callback);

    /**
     * Split a collection into a certain number of groups, and fill the first groups completely.
	 * 将集合分成一定数量的组，并完全填充第一组。
     *
     * @param  int  $numberOfGroups
     * @return static<int, static>
     */
    public function splitIn($numberOfGroups);

    /**
     * Sort through each item with a callback.
	 * 使用回调对每个项目进行排序
     *
     * @param  (callable(TValue, TValue): int)|null|int  $callback
     * @return static
     */
    public function sort($callback = null);

    /**
     * Sort items in descending order.
	 * 按降序对项目进行排序
     *
     * @param  int  $options
     * @return static
     */
    public function sortDesc($options = SORT_REGULAR);

    /**
     * Sort the collection using the given callback.
	 * 使用给定的回调对集合进行排序
     *
     * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string  $callback
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false);

    /**
     * Sort the collection in descending order using the given callback.
	 * 使用给定的回调按降序对集合进行排序
     *
     * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string  $callback
     * @param  int  $options
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR);

    /**
     * Sort the collection keys.
	 * 对集合键排序
     *
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortKeys($options = SORT_REGULAR, $descending = false);

    /**
     * Sort the collection keys in descending order.
	 * 按降序对集合键进行排序
     *
     * @param  int  $options
     * @return static
     */
    public function sortKeysDesc($options = SORT_REGULAR);

    /**
     * Sort the collection keys using a callback.
	 * 使用回调对集合键进行排序
     *
     * @param  callable(TKey, TKey): int  $callback
     * @return static
     */
    public function sortKeysUsing(callable $callback);

    /**
     * Get the sum of the given values.
	 * 得到给定值的和
     *
     * @param  (callable(TValue): mixed)|string|null  $callback
     * @return mixed
     */
    public function sum($callback = null);

    /**
     * Take the first or last {$limit} items.
	 * 取第一个或最后一个{$limit}项
     *
     * @param  int  $limit
     * @return static
     */
    public function take($limit);

    /**
     * Take items in the collection until the given condition is met.
	 * 获取集合中的项，直到满足给定条件。
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function takeUntil($value);

    /**
     * Take items in the collection while the given condition is met.
	 * 在满足给定条件时获取集合中的项
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function takeWhile($value);

    /**
     * Pass the collection to the given callback and then return it.
	 * 将集合传递给给定的回调函数，然后返回它。
     *
     * @param  callable(TValue): mixed  $callback
     * @return $this
     */
    public function tap(callable $callback);

    /**
     * Pass the enumerable to the given callback and return the result.
	 * 将可枚举对象传递给给定的回调函数并返回结果
     *
     * @template TPipeReturnType
     *
     * @param  callable($this): TPipeReturnType  $callback
     * @return TPipeReturnType
     */
    public function pipe(callable $callback);

    /**
     * Pass the collection into a new class.
	 * 将集合传递到一个新类
     *
     * @param  class-string  $class
     * @return mixed
     */
    public function pipeInto($class);

    /**
     * Pass the collection through a series of callable pipes and return the result.
	 * 将集合通过一系列可调用管道传递并返回结果
     *
     * @param  array<callable>  $pipes
     * @return mixed
     */
    public function pipeThrough($pipes);

    /**
     * Get the values of a given key.
	 * 获取给定键的值
     *
     * @param  string|array<array-key, string>  $value
     * @param  string|null  $key
     * @return static<int, mixed>
     */
    public function pluck($value, $key = null);

    /**
     * Create a collection of all elements that do not pass a given truth test.
	 * 创建一个未通过给定真值测试的所有元素的集合
     *
     * @param  (callable(TValue, TKey): bool)|bool|TValue  $callback
     * @return static
     */
    public function reject($callback = true);

    /**
     * Convert a flatten "dot" notation array into an expanded array.
	 * 将扁平的"点"表示法数组转换为展开的数组
     *
     * @return static
     */
    public function undot();

    /**
     * Return only unique items from the collection array.
	 * 只返回集合数组中唯一的项
     *
     * @param  (callable(TValue, TKey): mixed)|string|null  $key
     * @param  bool  $strict
     * @return static
     */
    public function unique($key = null, $strict = false);

    /**
     * Return only unique items from the collection array using strict comparison.
	 * 使用严格比较只返回集合数组中的唯一项
     *
     * @param  (callable(TValue, TKey): mixed)|string|null  $key
     * @return static
     */
    public function uniqueStrict($key = null);

    /**
     * Reset the keys on the underlying array.
	 * 重置基础数组上的键
     *
     * @return static<int, TValue>
     */
    public function values();

    /**
     * Pad collection to the specified length with a value.
	 * 使用值将集合垫到指定的长度
     *
     * @template TPadValue
     *
     * @param  int  $size
     * @param  TPadValue  $value
     * @return static<int, TValue|TPadValue>
     */
    public function pad($size, $value);

    /**
     * Get the values iterator.
	 * 获取值迭代器
     *
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable;

    /**
     * Count the number of items in the collection.
	 * 计算集合中的项数
     *
     * @return int
     */
    public function count(): int;

    /**
     * Count the number of items in the collection by a field or using a callback.
	 * 按字段或使用回调计算集合中的项数
     *
     * @param  (callable(TValue, TKey): array-key)|string|null  $countBy
     * @return static<array-key, int>
     */
    public function countBy($countBy = null);

    /**
     * Zip the collection together with one or more arrays.
	 * 将集合与一个或多个数组压缩在一起。
     *
     * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
     *      => [[1, 4], [2, 5], [3, 6]]
     *
     * @template TZipValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TZipValue>|iterable<array-key, TZipValue>  ...$items
     * @return static<int, static<int, TValue|TZipValue>>
     */
    public function zip($items);

    /**
     * Collect the values into a collection.
	 * 将这些值收集到一个集合中
     *
     * @return \Illuminate\Support\Collection<TKey, TValue>
     */
    public function collect();

    /**
     * Get the collection of items as a plain array.
	 * 获取作为普通数组的项集合
     *
     * @return array<TKey, mixed>
     */
    public function toArray();

    /**
     * Convert the object into something JSON serializable.
	 * 将对象转换为JSON可序列化的对象
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed;

    /**
     * Get the collection of items as JSON.
	 * 以JSON形式获取项目集合
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);

    /**
     * Get a CachingIterator instance.
	 * 获取一个CachingIterator实例
     *
     * @param  int  $flags
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING);

    /**
     * Convert the collection to its string representation.
	 * 将集合转换为其字符串表示形式
     *
     * @return string
     */
    public function __toString();

    /**
     * Indicate that the model's string representation should be escaped when __toString is invoked.
	 * 表明当__toString被调用时，模型的字符串表示应该被转义。
     *
     * @param  bool  $escape
     * @return $this
     */
    public function escapeWhenCastingToString($escape = true);

    /**
     * Add a method to the list of proxied methods.
	 * 向代理方法列表中添加一个方法
     *
     * @param  string  $method
     * @return void
     */
    public static function proxy($method);

    /**
     * Dynamically access collection proxies.
	 * 动态访问集合代理
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($key);
}
