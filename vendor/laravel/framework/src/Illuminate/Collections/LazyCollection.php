<?php
/**
 * Illuminate，集合，延迟集合
 */

namespace Illuminate\Support;

use ArrayIterator;
use Closure;
use DateTimeInterface;
use Generator;
use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;
use Illuminate\Support\Traits\EnumeratesValues;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use IteratorAggregate;
use stdClass;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements \Illuminate\Support\Enumerable<TKey, TValue>
 */
class LazyCollection implements CanBeEscapedWhenCastToString, Enumerable
{
    /**
     * @use \Illuminate\Support\Traits\EnumeratesValues<TKey, TValue>
     */
    use EnumeratesValues, Macroable;

    /**
     * The source from which to generate items.
	 * 要从中生成项的源
     *
     * @var (Closure(): \Generator<TKey, TValue, mixed, void>)|static|array<TKey, TValue>
     */
    public $source;

    /**
     * Create a new lazy collection instance.
	 * 创建一个新的惰性集合实例
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>|(Closure(): \Generator<TKey, TValue, mixed, void>)|self<TKey, TValue>|array<TKey, TValue>|null  $source
     * @return void
     */
    public function __construct($source = null)
    {
        if ($source instanceof Closure || $source instanceof self) {
            $this->source = $source;
        } elseif (is_null($source)) {
            $this->source = static::empty();
        } elseif ($source instanceof Generator) {
            throw new InvalidArgumentException(
                'Generators should not be passed directly to LazyCollection. Instead, pass a generator function.'
            );
        } else {
            $this->source = $this->getArrayableItems($source);
        }
    }

    /**
     * Create a new collection instance if the value isn't one already.
	 * 创建一个新的集合实例如果该值还没有
     *
     * @template TMakeKey of array-key
     * @template TMakeValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TMakeKey, TMakeValue>|iterable<TMakeKey, TMakeValue>|(Closure(): \Generator<TMakeKey, TMakeValue, mixed, void>)|self<TMakeKey, TMakeValue>|array<TMakeKey, TMakeValue>|null  $items
     * @return static<TMakeKey, TMakeValue>
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Create a collection with the given range.
	 * 创建具有给定范围的集合。
     *
     * @param  int  $from
     * @param  int  $to
     * @return static<int, int>
     */
    public static function range($from, $to)
    {
        return new static(function () use ($from, $to) {
            if ($from <= $to) {
                for (; $from <= $to; $from++) {
                    yield $from;
                }
            } else {
                for (; $from >= $to; $from--) {
                    yield $from;
                }
            }
        });
    }

    /**
     * Get all items in the enumerable.
	 * 获取枚举中的所有项
     *
     * @return array<TKey, TValue>
     */
    public function all()
    {
        if (is_array($this->source)) {
            return $this->source;
        }

        return iterator_to_array($this->getIterator());
    }

    /**
     * Eager load all items into a new lazy collection backed by an array.
	 * 将所有项加载到由数组支持的新惰性集合中
     *
     * @return static
     */
    public function eager()
    {
        return new static($this->all());
    }

    /**
     * Cache values as they're enumerated.
	 * 在枚举值时缓存它们
     *
     * @return static
     */
    public function remember()
    {
        $iterator = $this->getIterator();

        $iteratorIndex = 0;

        $cache = [];

        return new static(function () use ($iterator, &$iteratorIndex, &$cache) {
            for ($index = 0; true; $index++) {
                if (array_key_exists($index, $cache)) {
                    yield $cache[$index][0] => $cache[$index][1];

                    continue;
                }

                if ($iteratorIndex < $index) {
                    $iterator->next();

                    $iteratorIndex++;
                }

                if (! $iterator->valid()) {
                    break;
                }

                $cache[$index] = [$iterator->key(), $iterator->current()];

                yield $cache[$index][0] => $cache[$index][1];
            }
        });
    }

    /**
     * Get the average value of a given key.
	 * 获取给定键的平均值
     *
     * @param  (callable(TValue): float|int)|string|null  $callback
     * @return float|int|null
     */
    public function avg($callback = null)
    {
        return $this->collect()->avg($callback);
    }

    /**
     * Get the median of a given key.
	 * 求给定键的中值
     *
     * @param  string|array<array-key, string>|null  $key
     * @return float|int|null
     */
    public function median($key = null)
    {
        return $this->collect()->median($key);
    }

    /**
     * Get the mode of a given key.
	 * 获取给定键的模式
     *
     * @param  string|array<string>|null  $key
     * @return array<int, float|int>|null
     */
    public function mode($key = null)
    {
        return $this->collect()->mode($key);
    }

    /**
     * Collapse the collection of items into a single array.
	 * 将项目集合折叠成单个数组
     *
     * @return static<int, mixed>
     */
    public function collapse()
    {
        return new static(function () {
            foreach ($this as $values) {
                if (is_array($values) || $values instanceof Enumerable) {
                    foreach ($values as $value) {
                        yield $value;
                    }
                }
            }
        });
    }

    /**
     * Determine if an item exists in the enumerable.
	 * 确定枚举中是否存在项
     *
     * @param  (callable(TValue, TKey): bool)|TValue|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1 && $this->useAsCallable($key)) {
            $placeholder = new stdClass;

            /** @var callable $key */
            return $this->first($key, $placeholder) !== $placeholder;
        }

        if (func_num_args() === 1) {
            $needle = $key;

            foreach ($this as $value) {
                if ($value == $needle) {
                    return true;
                }
            }

            return false;
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Determine if an item exists, using strict comparison.
	 * 使用严格比较确定项是否存在
     *
     * @param  (callable(TValue): bool)|TValue|array-key  $key
     * @param  TValue|null  $value
     * @return bool
     */
    public function containsStrict($key, $value = null)
    {
        if (func_num_args() === 2) {
            return $this->contains(fn ($item) => data_get($item, $key) === $value);
        }

        if ($this->useAsCallable($key)) {
            return ! is_null($this->first($key));
        }

        foreach ($this as $item) {
            if ($item === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if an item is not contained in the enumerable.
	 * 确定枚举中是否不包含项
     *
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function doesntContain($key, $operator = null, $value = null)
    {
        return ! $this->contains(...func_get_args());
    }

    /**
     * Cross join the given iterables, returning all possible permutations.
	 * 交叉连接给定的可迭代对象，返回所有可能的排列。
     *
     * @template TCrossJoinKey
     * @template TCrossJoinValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TCrossJoinKey, TCrossJoinValue>|iterable<TCrossJoinKey, TCrossJoinValue>  ...$arrays
     * @return static<int, array<int, TValue|TCrossJoinValue>>
     */
    public function crossJoin(...$arrays)
    {
        return $this->passthru('crossJoin', func_get_args());
    }

    /**
     * Count the number of items in the collection by a field or using a callback.
	 * 按字段或使用回调计算集合中的项数
     *
     * @param  (callable(TValue, TKey): array-key)|string|null  $countBy
     * @return static<array-key, int>
     */
    public function countBy($countBy = null)
    {
        $countBy = is_null($countBy)
            ? $this->identity()
            : $this->valueRetriever($countBy);

        return new static(function () use ($countBy) {
            $counts = [];

            foreach ($this as $key => $value) {
                $group = $countBy($value, $key);

                if (empty($counts[$group])) {
                    $counts[$group] = 0;
                }

                $counts[$group]++;
            }

            yield from $counts;
        });
    }

    /**
     * Get the items that are not present in the given items.
	 * 获取在给定项中不存在的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
     * @return static
     */
    public function diff($items)
    {
        return $this->passthru('diff', func_get_args());
    }

    /**
     * Get the items that are not present in the given items, using the callback.
	 * 使用回调获取给定项中不存在的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
     * @param  callable(TValue, TValue): int  $callback
     * @return static
     */
    public function diffUsing($items, callable $callback)
    {
        return $this->passthru('diffUsing', func_get_args());
    }

    /**
     * Get the items whose keys and values are not present in the given items.
	 * 获取其键和值未出现在给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function diffAssoc($items)
    {
        return $this->passthru('diffAssoc', func_get_args());
    }

    /**
     * Get the items whose keys and values are not present in the given items, using the callback.
	 * 使用回调获取其键和值未出现在给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @param  callable(TKey, TKey): int  $callback
     * @return static
     */
    public function diffAssocUsing($items, callable $callback)
    {
        return $this->passthru('diffAssocUsing', func_get_args());
    }

    /**
     * Get the items whose keys are not present in the given items.
	 * 获取键不存在于给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function diffKeys($items)
    {
        return $this->passthru('diffKeys', func_get_args());
    }

    /**
     * Get the items whose keys are not present in the given items, using the callback.
	 * 使用回调获取键不在给定项中的项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @param  callable(TKey, TKey): int  $callback
     * @return static
     */
    public function diffKeysUsing($items, callable $callback)
    {
        return $this->passthru('diffKeysUsing', func_get_args());
    }

    /**
     * Retrieve duplicate items.
	 * 检索重复项
     *
     * @param  (callable(TValue): bool)|string|null  $callback
     * @param  bool  $strict
     * @return static
     */
    public function duplicates($callback = null, $strict = false)
    {
        return $this->passthru('duplicates', func_get_args());
    }

    /**
     * Retrieve duplicate items using strict comparison.
	 * 使用严格比较检索重复项
     *
     * @param  (callable(TValue): bool)|string|null  $callback
     * @return static
     */
    public function duplicatesStrict($callback = null)
    {
        return $this->passthru('duplicatesStrict', func_get_args());
    }

    /**
     * Get all items except for those with the specified keys.
	 * 获取除具有指定键的项之外的所有项
     *
     * @param  \Illuminate\Support\Enumerable<array-key, TKey>|array<array-key, TKey>  $keys
     * @return static
     */
    public function except($keys)
    {
        return $this->passthru('except', func_get_args());
    }

    /**
     * Run a filter over each of the items.
	 * 对每个项目运行一个过滤器
     *
     * @param  (callable(TValue, TKey): bool)|null  $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if (is_null($callback)) {
            $callback = fn ($value) => (bool) $value;
        }

        return new static(function () use ($callback) {
            foreach ($this as $key => $value) {
                if ($callback($value, $key)) {
                    yield $key => $value;
                }
            }
        });
    }

    /**
     * Get the first item from the enumerable passing the given truth test.
	 * 从通过给定真值测试的枚举中获取第一项
     *
     * @template TFirstDefault
     *
     * @param  (callable(TValue): bool)|null  $callback
     * @param  TFirstDefault|(\Closure(): TFirstDefault)  $default
     * @return TValue|TFirstDefault
     */
    public function first(callable $callback = null, $default = null)
    {
        $iterator = $this->getIterator();

        if (is_null($callback)) {
            if (! $iterator->valid()) {
                return value($default);
            }

            return $iterator->current();
        }

        foreach ($iterator as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return value($default);
    }

    /**
     * Get a flattened list of the items in the collection.
	 * 获取集合中项目的扁平列表
     *
     * @param  int  $depth
     * @return static<int, mixed>
     */
    public function flatten($depth = INF)
    {
        $instance = new static(function () use ($depth) {
            foreach ($this as $item) {
                if (! is_array($item) && ! $item instanceof Enumerable) {
                    yield $item;
                } elseif ($depth === 1) {
                    yield from $item;
                } else {
                    yield from (new static($item))->flatten($depth - 1);
                }
            }
        });

        return $instance->values();
    }

    /**
     * Flip the items in the collection.
	 * 翻转集合中的项目
     *
     * @return static<TValue, TKey>
     */
    public function flip()
    {
        return new static(function () {
            foreach ($this as $key => $value) {
                yield $value => $key;
            }
        });
    }

    /**
     * Get an item by key.
	 * 按键获取项目
     *
     * @template TGetDefault
     *
     * @param  TKey|null  $key
     * @param  TGetDefault|(\Closure(): TGetDefault)  $default
     * @return TValue|TGetDefault
     */
    public function get($key, $default = null)
    {
        if (is_null($key)) {
            return;
        }

        foreach ($this as $outerKey => $outerValue) {
            if ($outerKey == $key) {
                return $outerValue;
            }
        }

        return value($default);
    }

    /**
     * Group an associative array by a field or using a callback.
	 * 按字段或使用回调对关联数组进行分组
     *
     * @param  (callable(TValue, TKey): array-key)|array|string  $groupBy
     * @param  bool  $preserveKeys
     * @return static<array-key, static<array-key, TValue>>
     */
    public function groupBy($groupBy, $preserveKeys = false)
    {
        return $this->passthru('groupBy', func_get_args());
    }

    /**
     * Key an associative array by a field or using a callback.
	 * 通过字段或使用回调为关联数组设置键
     *
     * @param  (callable(TValue, TKey): array-key)|array|string  $keyBy
     * @return static<array-key, TValue>
     */
    public function keyBy($keyBy)
    {
        return new static(function () use ($keyBy) {
            $keyBy = $this->valueRetriever($keyBy);

            foreach ($this as $key => $item) {
                $resolvedKey = $keyBy($item, $key);

                if (is_object($resolvedKey)) {
                    $resolvedKey = (string) $resolvedKey;
                }

                yield $resolvedKey => $item;
            }
        });
    }

    /**
     * Determine if an item exists in the collection by key.
	 * 根据键确定集合中是否存在项
     *
     * @param  mixed  $key
     * @return bool
     */
    public function has($key)
    {
        $keys = array_flip(is_array($key) ? $key : func_get_args());
        $count = count($keys);

        foreach ($this as $key => $value) {
            if (array_key_exists($key, $keys) && --$count == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if any of the keys exist in the collection.
	 * 确定集合中是否存在任何键
     *
     * @param  mixed  $key
     * @return bool
     */
    public function hasAny($key)
    {
        $keys = array_flip(is_array($key) ? $key : func_get_args());

        foreach ($this as $key => $value) {
            if (array_key_exists($key, $keys)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Concatenate values of a given key as a string.
	 * 将给定键的值连接为字符串
     *
     * @param  callable|string  $value
     * @param  string|null  $glue
     * @return string
     */
    public function implode($value, $glue = null)
    {
        return $this->collect()->implode(...func_get_args());
    }

    /**
     * Intersect the collection with the given items.
	 * 将集合与给定的项目相交
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function intersect($items)
    {
        return $this->passthru('intersect', func_get_args());
    }

    /**
     * Intersect the collection with the given items, using the callback.
	 * 使用回调函数将集合与给定项相交
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
     * @param  callable(TValue, TValue): int  $callback
     * @return static
     */
    public function intersectUsing()
    {
        return $this->passthru('intersectUsing', func_get_args());
    }

    /**
     * Intersect the collection with the given items with additional index check.
	 * 通过附加索引检查将集合与给定项相交
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function intersectAssoc($items)
    {
        return $this->passthru('intersectAssoc', func_get_args());
    }

    /**
     * Intersect the collection with the given items with additional index check, using the callback.
	 * 使用回调函数，将集合与具有附加索引检查的给定项相交。
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>|iterable<array-key, TValue>  $items
     * @param  callable(TValue, TValue): int  $callback
     * @return static
     */
    public function intersectAssocUsing($items, callable $callback)
    {
        return $this->passthru('intersectAssocUsing', func_get_args());
    }

    /**
     * Intersect the collection with the given items by key.
	 * 通过键将集合与给定的项目相交
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function intersectByKeys($items)
    {
        return $this->passthru('intersectByKeys', func_get_args());
    }

    /**
     * Determine if the items are empty or not.
	 * 确定项是否为空
     *
     * @return bool
     */
    public function isEmpty()
    {
        return ! $this->getIterator()->valid();
    }

    /**
     * Determine if the collection contains a single item.
	 * 确定集合是否包含单个项
     *
     * @return bool
     */
    public function containsOneItem()
    {
        return $this->take(2)->count() === 1;
    }

    /**
     * Join all items from the collection using a string. The final items can use a separate glue string.
	 * 使用字符串连接集合中的所有项。最后的项目可以使用一个单独的胶水线。
     *
     * @param  string  $glue
     * @param  string  $finalGlue
     * @return string
     */
    public function join($glue, $finalGlue = '')
    {
        return $this->collect()->join(...func_get_args());
    }

    /**
     * Get the keys of the collection items.
	 * 获得收集项目的密钥
     *
     * @return static<int, TKey>
     */
    public function keys()
    {
        return new static(function () {
            foreach ($this as $key => $value) {
                yield $key;
            }
        });
    }

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
    public function last(callable $callback = null, $default = null)
    {
        $needle = $placeholder = new stdClass;

        foreach ($this as $key => $value) {
            if (is_null($callback) || $callback($value, $key)) {
                $needle = $value;
            }
        }

        return $needle === $placeholder ? value($default) : $needle;
    }

    /**
     * Get the values of a given key.
	 * 获取给定键的值
     *
     * @param  string|array<array-key, string>  $value
     * @param  string|null  $key
     * @return static<int, mixed>
     */
    public function pluck($value, $key = null)
    {
        return new static(function () use ($value, $key) {
            [$value, $key] = $this->explodePluckParameters($value, $key);

            foreach ($this as $item) {
                $itemValue = data_get($item, $value);

                if (is_null($key)) {
                    yield $itemValue;
                } else {
                    $itemKey = data_get($item, $key);

                    if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                        $itemKey = (string) $itemKey;
                    }

                    yield $itemKey => $itemValue;
                }
            }
        });
    }

    /**
     * Run a map over each of the items.
	 * 在每个项目上运行一张地图
     *
     * @template TMapValue
     *
     * @param  callable(TValue, TKey): TMapValue  $callback
     * @return static<TKey, TMapValue>
     */
    public function map(callable $callback)
    {
        return new static(function () use ($callback) {
            foreach ($this as $key => $value) {
                yield $key => $callback($value, $key);
            }
        });
    }

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
    public function mapToDictionary(callable $callback)
    {
        return $this->passthru('mapToDictionary', func_get_args());
    }

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
    public function mapWithKeys(callable $callback)
    {
        return new static(function () use ($callback) {
            foreach ($this as $key => $value) {
                yield from $callback($value, $key);
            }
        });
    }

    /**
     * Merge the collection with the given items.
	 * 将集合与给定的项合并
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function merge($items)
    {
        return $this->passthru('merge', func_get_args());
    }

    /**
     * Recursively merge the collection with the given items.
	 * 递归地将集合与给定项合并
     *
     * @template TMergeRecursiveValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TMergeRecursiveValue>|iterable<TKey, TMergeRecursiveValue>  $items
     * @return static<TKey, TValue|TMergeRecursiveValue>
     */
    public function mergeRecursive($items)
    {
        return $this->passthru('mergeRecursive', func_get_args());
    }

    /**
     * Create a collection by using this collection for keys and another for its values.
	 * 创建一个集合，将这个集合用于键，另一个用于它的值。
     *
     * @template TCombineValue
     *
     * @param  \IteratorAggregate<array-key, TCombineValue>|array<array-key, TCombineValue>|(callable(): \Generator<array-key, TCombineValue>)  $values
     * @return static<TValue, TCombineValue>
     */
    public function combine($values)
    {
        return new static(function () use ($values) {
            $values = $this->makeIterator($values);

            $errorMessage = 'Both parameters should have an equal number of elements';

            foreach ($this as $key) {
                if (! $values->valid()) {
                    trigger_error($errorMessage, E_USER_WARNING);

                    break;
                }

                yield $key => $values->current();

                $values->next();
            }

            if ($values->valid()) {
                trigger_error($errorMessage, E_USER_WARNING);
            }
        });
    }

    /**
     * Union the collection with the given items.
	 * 将集合与给定项联合
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function union($items)
    {
        return $this->passthru('union', func_get_args());
    }

    /**
     * Create a new collection consisting of every n-th element.
	 * 创建一个包含每n个元素的新集合
     *
     * @param  int  $step
     * @param  int  $offset
     * @return static
     */
    public function nth($step, $offset = 0)
    {
        return new static(function () use ($step, $offset) {
            $position = 0;

            foreach ($this->slice($offset) as $item) {
                if ($position % $step === 0) {
                    yield $item;
                }

                $position++;
            }
        });
    }

    /**
     * Get the items with the specified keys.
	 * 获取具有指定键的项
     *
     * @param  \Illuminate\Support\Enumerable<array-key, TKey>|array<array-key, TKey>|string  $keys
     * @return static
     */
    public function only($keys)
    {
        if ($keys instanceof Enumerable) {
            $keys = $keys->all();
        } elseif (! is_null($keys)) {
            $keys = is_array($keys) ? $keys : func_get_args();
        }

        return new static(function () use ($keys) {
            if (is_null($keys)) {
                yield from $this;
            } else {
                $keys = array_flip($keys);

                foreach ($this as $key => $value) {
                    if (array_key_exists($key, $keys)) {
                        yield $key => $value;

                        unset($keys[$key]);

                        if (empty($keys)) {
                            break;
                        }
                    }
                }
            }
        });
    }

    /**
     * Push all of the given items onto the collection.
	 * 将所有给定的项推入集合
     *
     * @param  iterable<array-key, TValue>  $source
     * @return static
     */
    public function concat($source)
    {
        return (new static(function () use ($source) {
            yield from $this;
            yield from $source;
        }))->values();
    }

    /**
     * Get one or a specified number of items randomly from the collection.
	 * 从集合中随机获取一个或指定数量的项
     *
     * @param  int|null  $number
     * @return static<int, TValue>|TValue
     *
     * @throws \InvalidArgumentException
     */
    public function random($number = null)
    {
        $result = $this->collect()->random(...func_get_args());

        return is_null($number) ? $result : new static($result);
    }

    /**
     * Replace the collection items with the given items.
	 * 用给定的项替换集合项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function replace($items)
    {
        return new static(function () use ($items) {
            $items = $this->getArrayableItems($items);

            foreach ($this as $key => $value) {
                if (array_key_exists($key, $items)) {
                    yield $key => $items[$key];

                    unset($items[$key]);
                } else {
                    yield $key => $value;
                }
            }

            foreach ($items as $key => $value) {
                yield $key => $value;
            }
        });
    }

    /**
     * Recursively replace the collection items with the given items.
	 * 递归地将集合项替换为给定项
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<TKey, TValue>|iterable<TKey, TValue>  $items
     * @return static
     */
    public function replaceRecursive($items)
    {
        return $this->passthru('replaceRecursive', func_get_args());
    }

    /**
     * Reverse items order.
	 * 颠倒项目顺序
     *
     * @return static
     */
    public function reverse()
    {
        return $this->passthru('reverse', func_get_args());
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
	 * 在集合中搜索给定的值，如果成功则返回相应的键。
     *
     * @param  TValue|(callable(TValue,TKey): bool)  $value
     * @param  bool  $strict
     * @return TKey|bool
     */
    public function search($value, $strict = false)
    {
        /** @var (callable(TValue,TKey): bool) $predicate */
        $predicate = $this->useAsCallable($value)
            ? $value
            : function ($item) use ($value, $strict) {
                return $strict ? $item === $value : $item == $value;
            };

        foreach ($this as $key => $item) {
            if ($predicate($item, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Shuffle the items in the collection.
	 * 对集合中的项进行洗牌
     *
     * @param  int|null  $seed
     * @return static
     */
    public function shuffle($seed = null)
    {
        return $this->passthru('shuffle', func_get_args());
    }

    /**
     * Create chunks representing a "sliding window" view of the items in the collection.
	 * 创建块，表示集合中项的"滑动窗口"视图。
     *
     * @param  int  $size
     * @param  int  $step
     * @return static<int, static>
     */
    public function sliding($size = 2, $step = 1)
    {
        return new static(function () use ($size, $step) {
            $iterator = $this->getIterator();

            $chunk = [];

            while ($iterator->valid()) {
                $chunk[$iterator->key()] = $iterator->current();

                if (count($chunk) == $size) {
                    yield (new static($chunk))->tap(function () use (&$chunk, $step) {
                        $chunk = array_slice($chunk, $step, null, true);
                    });

                    // If the $step between chunks is bigger than each chunk's $size
                    // we will skip the extra items (which should never be in any
                    // chunk) before we continue to the next chunk in the loop.
                    if ($step > $size) {
                        $skip = $step - $size;

                        for ($i = 0; $i < $skip && $iterator->valid(); $i++) {
                            $iterator->next();
                        }
                    }
                }

                $iterator->next();
            }
        });
    }

    /**
     * Skip the first {$count} items.
	 * 跳过第一个{$count}项
     *
     * @param  int  $count
     * @return static
     */
    public function skip($count)
    {
        return new static(function () use ($count) {
            $iterator = $this->getIterator();

            while ($iterator->valid() && $count--) {
                $iterator->next();
            }

            while ($iterator->valid()) {
                yield $iterator->key() => $iterator->current();

                $iterator->next();
            }
        });
    }

    /**
     * Skip items in the collection until the given condition is met.
	 * 跳过集合中的项，直到满足给定的条件。
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function skipUntil($value)
    {
        $callback = $this->useAsCallable($value) ? $value : $this->equality($value);

        return $this->skipWhile($this->negate($callback));
    }

    /**
     * Skip items in the collection while the given condition is met.
	 * 在满足给定条件时跳过集合中的项
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function skipWhile($value)
    {
        $callback = $this->useAsCallable($value) ? $value : $this->equality($value);

        return new static(function () use ($callback) {
            $iterator = $this->getIterator();

            while ($iterator->valid() && $callback($iterator->current(), $iterator->key())) {
                $iterator->next();
            }

            while ($iterator->valid()) {
                yield $iterator->key() => $iterator->current();

                $iterator->next();
            }
        });
    }

    /**
     * Get a slice of items from the enumerable.
	 * 从可枚举对象中获取项目的切片
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @return static
     */
    public function slice($offset, $length = null)
    {
        if ($offset < 0 || $length < 0) {
            return $this->passthru('slice', func_get_args());
        }

        $instance = $this->skip($offset);

        return is_null($length) ? $instance : $instance->take($length);
    }

    /**
     * Split a collection into a certain number of groups.
	 * 将一个集合分成一定数量的组
     *
     * @param  int  $numberOfGroups
     * @return static<int, static>
     */
    public function split($numberOfGroups)
    {
        return $this->passthru('split', func_get_args());
    }

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
    public function sole($key = null, $operator = null, $value = null)
    {
        $filter = func_num_args() > 1
            ? $this->operatorForWhere(...func_get_args())
            : $key;

        return $this
            ->unless($filter == null)
            ->filter($filter)
            ->take(2)
            ->collect()
            ->sole();
    }

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
    public function firstOrFail($key = null, $operator = null, $value = null)
    {
        $filter = func_num_args() > 1
            ? $this->operatorForWhere(...func_get_args())
            : $key;

        return $this
            ->unless($filter == null)
            ->filter($filter)
            ->take(1)
            ->collect()
            ->firstOrFail();
    }

    /**
     * Chunk the collection into chunks of the given size.
	 * 将集合分成给定大小的块
     *
     * @param  int  $size
     * @return static<int, static>
     */
    public function chunk($size)
    {
        if ($size <= 0) {
            return static::empty();
        }

        return new static(function () use ($size) {
            $iterator = $this->getIterator();

            while ($iterator->valid()) {
                $chunk = [];

                while (true) {
                    $chunk[$iterator->key()] = $iterator->current();

                    if (count($chunk) < $size) {
                        $iterator->next();

                        if (! $iterator->valid()) {
                            break;
                        }
                    } else {
                        break;
                    }
                }

                yield new static($chunk);

                $iterator->next();
            }
        });
    }

    /**
     * Split a collection into a certain number of groups, and fill the first groups completely.
	 * 将集合分成一定数量的组，并完全填充第一组。
     *
     * @param  int  $numberOfGroups
     * @return static<int, static>
     */
    public function splitIn($numberOfGroups)
    {
        return $this->chunk(ceil($this->count() / $numberOfGroups));
    }

    /**
     * Chunk the collection into chunks with a callback.
	 * 使用回调将集合分成块
     *
     * @param  callable(TValue, TKey, Collection<TKey, TValue>): bool  $callback
     * @return static<int, static<int, TValue>>
     */
    public function chunkWhile(callable $callback)
    {
        return new static(function () use ($callback) {
            $iterator = $this->getIterator();

            $chunk = new Collection;

            if ($iterator->valid()) {
                $chunk[$iterator->key()] = $iterator->current();

                $iterator->next();
            }

            while ($iterator->valid()) {
                if (! $callback($iterator->current(), $iterator->key(), $chunk)) {
                    yield new static($chunk);

                    $chunk = new Collection;
                }

                $chunk[$iterator->key()] = $iterator->current();

                $iterator->next();
            }

            if ($chunk->isNotEmpty()) {
                yield new static($chunk);
            }
        });
    }

    /**
     * Sort through each item with a callback.
	 * 使用回调对每个项目进行排序
     *
     * @param  (callable(TValue, TValue): int)|null|int  $callback
     * @return static
     */
    public function sort($callback = null)
    {
        return $this->passthru('sort', func_get_args());
    }

    /**
     * Sort items in descending order.
	 * 按降序对项目进行排序
     *
     * @param  int  $options
     * @return static
     */
    public function sortDesc($options = SORT_REGULAR)
    {
        return $this->passthru('sortDesc', func_get_args());
    }

    /**
     * Sort the collection using the given callback.
	 * 使用给定的回调对集合进行排序
     *
     * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string  $callback
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        return $this->passthru('sortBy', func_get_args());
    }

    /**
     * Sort the collection in descending order using the given callback.
	 * 使用给定的回调按降序对集合进行排序
     *
     * @param  array<array-key, (callable(TValue, TValue): mixed)|(callable(TValue, TKey): mixed)|string|array{string, string}>|(callable(TValue, TKey): mixed)|string  $callback
     * @param  int  $options
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->passthru('sortByDesc', func_get_args());
    }

    /**
     * Sort the collection keys.
	 * 对集合键排序
     *
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        return $this->passthru('sortKeys', func_get_args());
    }

    /**
     * Sort the collection keys in descending order.
	 * 按降序对集合键进行排序
     *
     * @param  int  $options
     * @return static
     */
    public function sortKeysDesc($options = SORT_REGULAR)
    {
        return $this->passthru('sortKeysDesc', func_get_args());
    }

    /**
     * Sort the collection keys using a callback.
	 * 使用回调对集合键进行排序
     *
     * @param  callable(TKey, TKey): int  $callback
     * @return static
     */
    public function sortKeysUsing(callable $callback)
    {
        return $this->passthru('sortKeysUsing', func_get_args());
    }

    /**
     * Take the first or last {$limit} items.
	 * 取第一个或最后一个{$limit}项
     *
     * @param  int  $limit
     * @return static
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->passthru('take', func_get_args());
        }

        return new static(function () use ($limit) {
            $iterator = $this->getIterator();

            while ($limit--) {
                if (! $iterator->valid()) {
                    break;
                }

                yield $iterator->key() => $iterator->current();

                if ($limit) {
                    $iterator->next();
                }
            }
        });
    }

    /**
     * Take items in the collection until the given condition is met.
	 * 获取集合中的项，直到满足给定条件。
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function takeUntil($value)
    {
        /** @var callable(TValue, TKey): bool $callback */
        $callback = $this->useAsCallable($value) ? $value : $this->equality($value);

        return new static(function () use ($callback) {
            foreach ($this as $key => $item) {
                if ($callback($item, $key)) {
                    break;
                }

                yield $key => $item;
            }
        });
    }

    /**
     * Take items in the collection until a given point in time.
	 * 在给定的时间点之前获取集合中的项目
     *
     * @param  \DateTimeInterface  $timeout
     * @return static
     */
    public function takeUntilTimeout(DateTimeInterface $timeout)
    {
        $timeout = $timeout->getTimestamp();

        return new static(function () use ($timeout) {
            if ($this->now() >= $timeout) {
                return;
            }

            foreach ($this as $key => $value) {
                yield $key => $value;

                if ($this->now() >= $timeout) {
                    break;
                }
            }
        });
    }

    /**
     * Take items in the collection while the given condition is met.
	 * 在满足给定条件时获取集合中的项
     *
     * @param  TValue|callable(TValue,TKey): bool  $value
     * @return static
     */
    public function takeWhile($value)
    {
        /** @var callable(TValue, TKey): bool $callback */
        $callback = $this->useAsCallable($value) ? $value : $this->equality($value);

        return $this->takeUntil(fn ($item, $key) => ! $callback($item, $key));
    }

    /**
     * Pass each item in the collection to the given callback, lazily.
	 * 惰性地将集合中的每个项传递给给定的回调函数
     *
     * @param  callable(TValue, TKey): mixed  $callback
     * @return static
     */
    public function tapEach(callable $callback)
    {
        return new static(function () use ($callback) {
            foreach ($this as $key => $value) {
                $callback($value, $key);

                yield $key => $value;
            }
        });
    }

    /**
     * Convert a flatten "dot" notation array into an expanded array.
	 * 将扁平的"点"表示法数组转换为展开的数组
     *
     * @return static
     */
    public function undot()
    {
        return $this->passthru('undot', []);
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

        return new static(function () use ($callback, $strict) {
            $exists = [];

            foreach ($this as $key => $item) {
                if (! in_array($id = $callback($item, $key), $exists, $strict)) {
                    yield $key => $item;

                    $exists[] = $id;
                }
            }
        });
    }

    /**
     * Reset the keys on the underlying array.
	 * 重置基础数组上的键
     *
     * @return static<int, TValue>
     */
    public function values()
    {
        return new static(function () {
            foreach ($this as $item) {
                yield $item;
            }
        });
    }

    /**
     * Zip the collection together with one or more arrays.
	 * 将集合与一个或多个数组压缩在一起
     *
     * e.g. new LazyCollection([1, 2, 3])->zip([4, 5, 6]);
     *      => [[1, 4], [2, 5], [3, 6]]
     *
     * @template TZipValue
     *
     * @param  \Illuminate\Contracts\Support\Arrayable<array-key, TZipValue>|iterable<array-key, TZipValue>  ...$items
     * @return static<int, static<int, TValue|TZipValue>>
     */
    public function zip($items)
    {
        $iterables = func_get_args();

        return new static(function () use ($iterables) {
            $iterators = Collection::make($iterables)->map(function ($iterable) {
                return $this->makeIterator($iterable);
            })->prepend($this->getIterator());

            while ($iterators->contains->valid()) {
                yield new static($iterators->map->current());

                $iterators->each->next();
            }
        });
    }

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
    public function pad($size, $value)
    {
        if ($size < 0) {
            return $this->passthru('pad', func_get_args());
        }

        return new static(function () use ($size, $value) {
            $yielded = 0;

            foreach ($this as $index => $item) {
                yield $index => $item;

                $yielded++;
            }

            while ($yielded++ < $size) {
                yield $value;
            }
        });
    }

    /**
     * Get the values iterator.
	 * 获取值迭代器
     *
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return $this->makeIterator($this->source);
    }

    /**
     * Count the number of items in the collection.
	 * 计算集合中的项数
     *
     * @return int
     */
    public function count(): int
    {
        if (is_array($this->source)) {
            return count($this->source);
        }

        return iterator_count($this->getIterator());
    }

    /**
     * Make an iterator from the given source.
	 * 从给定的源代码创建一个迭代器
     *
     * @template TIteratorKey of array-key
     * @template TIteratorValue
     *
     * @param  \IteratorAggregate<TIteratorKey, TIteratorValue>|array<TIteratorKey, TIteratorValue>|(callable(): \Generator<TIteratorKey, TIteratorValue>)  $source
     * @return \Traversable<TIteratorKey, TIteratorValue>
     */
    protected function makeIterator($source)
    {
        if ($source instanceof IteratorAggregate) {
            return $source->getIterator();
        }

        if (is_array($source)) {
            return new ArrayIterator($source);
        }

        if (is_callable($source)) {
            $maybeTraversable = $source();

            return $maybeTraversable instanceof Traversable
                ? $maybeTraversable
                : new ArrayIterator(Arr::wrap($maybeTraversable));
        }

        return new ArrayIterator((array) $source);
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
	 * 爆炸传递给“pluck”的“value”和“key”参
     *
     * @param  string|string[]  $value
     * @param  string|string[]|null  $key
     * @return array{string[],string[]|null}
     */
    protected function explodePluckParameters($value, $key)
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Pass this lazy collection through a method on the collection class.
	 * 通过集合类上的方法传递此惰性集合
     *
     * @param  string  $method
     * @param  array<mixed>  $params
     * @return static
     */
    protected function passthru($method, array $params)
    {
        return new static(function () use ($method, $params) {
            yield from $this->collect()->$method(...$params);
        });
    }

    /**
     * Get the current time.
	 * 获取当前时间
     *
     * @return int
     */
    protected function now()
    {
        return time();
    }
}
