<?php
/**
 * Illuminate，分页，游标分页器抽象
 */

namespace Illuminate\Pagination;

use ArrayAccess;
use Closure;
use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Tappable;
use Traversable;

/**
 * @mixin \Illuminate\Support\Collection
 */
abstract class AbstractCursorPaginator implements Htmlable
{
    use ForwardsCalls, Tappable;

    /**
     * All of the items being paginated.
	 * 所有被分页的项
     *
     * @var \Illuminate\Support\Collection
     */
    protected $items;

    /**
     * The number of items to be shown per page.
	 * 每页显示的条目数
     *
     * @var int
     */
    protected $perPage;

    /**
     * The base path to assign to all URLs.
	 * 分配给所有url的基本路径
     *
     * @var string
     */
    protected $path = '/';

    /**
     * The query parameters to add to all URLs.
	 * 要添加到所有url的查询参数
     *
     * @var array
     */
    protected $query = [];

    /**
     * The URL fragment to add to all URLs.
	 * 要添加到所有URL的URL片段
     *
     * @var string|null
     */
    protected $fragment;

    /**
     * The cursor string variable used to store the page.
	 * 用于存储页面的游标字符串变量
     *
     * @var string
     */
    protected $cursorName = 'cursor';

    /**
     * The current cursor.
	 * 当前游标
     *
     * @var \Illuminate\Pagination\Cursor|null
     */
    protected $cursor;

    /**
     * The paginator parameters for the cursor.
	 * 游标的分页器参数
     *
     * @var array
     */
    protected $parameters;

    /**
     * The paginator options.
	 * 分页器选项
     *
     * @var array
     */
    protected $options;

    /**
     * The current cursor resolver callback.
	 * 当前游标解析器回调
     *
     * @var \Closure
     */
    protected static $currentCursorResolver;

    /**
     * Get the URL for a given cursor.
	 * 获取给定游标的URL
     *
     * @param  \Illuminate\Pagination\Cursor|null  $cursor
     * @return string
     */
    public function url($cursor)
    {
        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
		// 如果我们有任何额外的查询字符串键/值对。
        $parameters = is_null($cursor) ? [] : [$this->cursorName => $cursor->encode()];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        return $this->path()
            .(str_contains($this->path(), '?') ? '&' : '?')
            .Arr::query($parameters)
            .$this->buildFragment();
    }

    /**
     * Get the URL for the previous page.
	 * 获取前一页的URL
     *
     * @return string|null
     */
    public function previousPageUrl()
    {
        if (is_null($previousCursor = $this->previousCursor())) {
            return null;
        }

        return $this->url($previousCursor);
    }

    /**
     * The URL for the next page, or null.
	 * 下一页的URL，或者为空。
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if (is_null($nextCursor = $this->nextCursor())) {
            return null;
        }

        return $this->url($nextCursor);
    }

    /**
     * Get the "cursor" that points to the previous set of items.
	 * 获取指向前一组项的"游标"
     *
     * @return \Illuminate\Pagination\Cursor|null
     */
    public function previousCursor()
    {
        if (is_null($this->cursor) ||
            ($this->cursor->pointsToPreviousItems() && ! $this->hasMore)) {
            return null;
        }

        if ($this->items->isEmpty()) {
            return null;
        }

        return $this->getCursorForItem($this->items->first(), false);
    }

    /**
     * Get the "cursor" that points to the next set of items.
	 * 获取指向下一组项目的"光标"
     *
     * @return \Illuminate\Pagination\Cursor|null
     */
    public function nextCursor()
    {
        if ((is_null($this->cursor) && ! $this->hasMore) ||
            (! is_null($this->cursor) && $this->cursor->pointsToNextItems() && ! $this->hasMore)) {
            return null;
        }

        if ($this->items->isEmpty()) {
            return null;
        }

        return $this->getCursorForItem($this->items->last(), true);
    }

    /**
     * Get a cursor instance for the given item.
	 * 获取给定项的游标实例
     *
     * @param  \ArrayAccess|\stdClass  $item
     * @param  bool  $isNext
     * @return \Illuminate\Pagination\Cursor
     */
    public function getCursorForItem($item, $isNext = true)
    {
        return new Cursor($this->getParametersForItem($item), $isNext);
    }

    /**
     * Get the cursor parameters for a given object.
	 * 获取给定对象的游标参数
     *
     * @param  \ArrayAccess|\stdClass  $item
     * @return array
     *
     * @throws \Exception
     */
    public function getParametersForItem($item)
    {
        return collect($this->parameters)
            ->flip()
            ->map(function ($_, $parameterName) use ($item) {
                if ($item instanceof JsonResource) {
                    $item = $item->resource;
                }

                if ($item instanceof Model &&
                    ! is_null($parameter = $this->getPivotParameterForItem($item, $parameterName))) {
                    return $parameter;
                } elseif ($item instanceof ArrayAccess || is_array($item)) {
                    return $this->ensureParameterIsPrimitive(
                        $item[$parameterName] ?? $item[Str::afterLast($parameterName, '.')]
                    );
                } elseif (is_object($item)) {
                    return $this->ensureParameterIsPrimitive(
                        $item->{$parameterName} ?? $item->{Str::afterLast($parameterName, '.')}
                    );
                }

                throw new Exception('Only arrays and objects are supported when cursor paginating items.');
            })->toArray();
    }

    /**
     * Get the cursor parameter value from a pivot model if applicable.
	 * 如果适用，从数据透视模型获取游标参数值。
     *
     * @param  \ArrayAccess|\stdClass  $item
     * @param  string  $parameterName
     * @return string|null
     */
    protected function getPivotParameterForItem($item, $parameterName)
    {
        $table = Str::beforeLast($parameterName, '.');

        foreach ($item->getRelations() as $relation) {
            if ($relation instanceof Pivot && $relation->getTable() === $table) {
                return $this->ensureParameterIsPrimitive(
                    $relation->getAttribute(Str::afterLast($parameterName, '.'))
                );
            }
        }
    }

    /**
     * Ensure the parameter is a primitive type.
	 * 确保参数是基本类型
     *
     * This can resolve issues that arise the developer uses a value object for an attribute.
     *
     * @param  mixed  $parameter
     * @return mixed
     */
    protected function ensureParameterIsPrimitive($parameter)
    {
        return is_object($parameter) && method_exists($parameter, '__toString')
                        ? (string) $parameter
                        : $parameter;
    }

    /**
     * Get / set the URL fragment to be appended to URLs.
	 * 获取/设置要附加到URL的URL片段
     *
     * @param  string|null  $fragment
     * @return $this|string|null
     */
    public function fragment($fragment = null)
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }

        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Add a set of query string values to the paginator.
	 * 向分页器添加一组查询字符串值。
     *
     * @param  array|string|null  $key
     * @param  string|null  $value
     * @return $this
     */
    public function appends($key, $value = null)
    {
        if (is_null($key)) {
            return $this;
        }

        if (is_array($key)) {
            return $this->appendArray($key);
        }

        return $this->addQuery($key, $value);
    }

    /**
     * Add an array of query string values.
	 * 添加查询字符串值数组
     *
     * @param  array  $keys
     * @return $this
     */
    protected function appendArray(array $keys)
    {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }

        return $this;
    }

    /**
     * Add all current query string values to the paginator.
	 * 将所有当前查询字符串值添加到分页器
     *
     * @return $this
     */
    public function withQueryString()
    {
        if (! is_null($query = Paginator::resolveQueryString())) {
            return $this->appends($query);
        }

        return $this;
    }

    /**
     * Add a query string value to the paginator.
	 * 向分页器添加查询字符串值
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    protected function addQuery($key, $value)
    {
        if ($key !== $this->cursorName) {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Build the full fragment portion of a URL.
	 * 构建URL的完整片段部分
     *
     * @return string
     */
    protected function buildFragment()
    {
        return $this->fragment ? '#'.$this->fragment : '';
    }

    /**
     * Load a set of relationships onto the mixed relationship collection.
	 * 将一组关系加载到混合关系集合中
     *
     * @param  string  $relation
     * @param  array  $relations
     * @return $this
     */
    public function loadMorph($relation, $relations)
    {
        $this->getCollection()->loadMorph($relation, $relations);

        return $this;
    }

    /**
     * Load a set of relationship counts onto the mixed relationship collection.
	 * 将一组关系计数加载到混合关系集合上
     *
     * @param  string  $relation
     * @param  array  $relations
     * @return $this
     */
    public function loadMorphCount($relation, $relations)
    {
        $this->getCollection()->loadMorphCount($relation, $relations);

        return $this;
    }

    /**
     * Get the slice of items being paginated.
	 * 获取正在分页的项的切片
     *
     * @return array
     */
    public function items()
    {
        return $this->items->all();
    }

    /**
     * Transform each item in the slice of items using a callback.
	 * 使用回调转换项片中的每个项
     *
     * @param  callable  $callback
     * @return $this
     */
    public function through(callable $callback)
    {
        $this->items->transform($callback);

        return $this;
    }

    /**
     * Get the number of items shown per page.
	 * 获取每页显示的项目数
     *
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Get the current cursor being paginated.
	 * 获取正在分页的当前游
     *
     * @return \Illuminate\Pagination\Cursor|null
     */
    public function cursor()
    {
        return $this->cursor;
    }

    /**
     * Get the query string variable used to store the cursor.
	 * 获取用于存储游标的查询字符串变量
     *
     * @return string
     */
    public function getCursorName()
    {
        return $this->cursorName;
    }

    /**
     * Set the query string variable used to store the cursor.
	 * 设置用于存储游标的查询字符串变量
     *
     * @param  string  $name
     * @return $this
     */
    public function setCursorName($name)
    {
        $this->cursorName = $name;

        return $this;
    }

    /**
     * Set the base path to assign to all URLs.
	 * 设置分配给所有url的基本路径
     *
     * @param  string  $path
     * @return $this
     */
    public function withPath($path)
    {
        return $this->setPath($path);
    }

    /**
     * Set the base path to assign to all URLs.
	 * 设置分配给所有url的基本路径
     *
     * @param  string  $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the base path for paginator generated URLs.
	 * 获取分页器生成的url的基本路径
     *
     * @return string|null
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Resolve the current cursor or return the default value.
	 * 解析当前游标或返回默认值
     *
     * @param  string  $cursorName
     * @return \Illuminate\Pagination\Cursor|null
     */
    public static function resolveCurrentCursor($cursorName = 'cursor', $default = null)
    {
        if (isset(static::$currentCursorResolver)) {
            return call_user_func(static::$currentCursorResolver, $cursorName);
        }

        return $default;
    }

    /**
     * Set the current cursor resolver callback.
	 * 设置当前游标解析器回调
     *
     * @param  \Closure  $resolver
     * @return void
     */
    public static function currentCursorResolver(Closure $resolver)
    {
        static::$currentCursorResolver = $resolver;
    }

    /**
     * Get an instance of the view factory from the resolver.
	 * 从解析器获取视图工厂的实例
     *
     * @return \Illuminate\Contracts\View\Factory
     */
    public static function viewFactory()
    {
        return Paginator::viewFactory();
    }

    /**
     * Get an iterator for the items.
	 * 获取项的迭代器
     *
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return $this->items->getIterator();
    }

    /**
     * Determine if the list of items is empty.
	 * 确定项目列表是否为空
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Determine if the list of items is not empty.
	 * 确定项目列表是否为空
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return $this->items->isNotEmpty();
    }

    /**
     * Get the number of items for the current page.
	 * 获取当前页面的项数
     *
     * @return int
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Get the paginator's underlying collection.
	 * 获取分页器的底层集合
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCollection()
    {
        return $this->items;
    }

    /**
     * Set the paginator's underlying collection.
	 * 设置分页器的基础集合
     *
     * @param  \Illuminate\Support\Collection  $collection
     * @return $this
     */
    public function setCollection(Collection $collection)
    {
        $this->items = $collection;

        return $this;
    }

    /**
     * Get the paginator options.
	 * 获取分页器选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Determine if the given item exists.
	 * 确定给定项是否存在
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->items->has($key);
    }

    /**
     * Get the item at the given offset.
	 * 获取给定偏移量处的项
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->items->get($key);
    }

    /**
     * Set the item at the given offset.
	 * 在给定的偏移量处设置项
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->items->put($key, $value);
    }

    /**
     * Unset the item at the given key.
	 * 取消给定键处的项设置
     *
     * @param  mixed  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->items->forget($key);
    }

    /**
     * Render the contents of the paginator to HTML.
	 * 将分页器的内容呈现为HTML
     *
     * @return string
     */
    public function toHtml()
    {
        return (string) $this->render();
    }

    /**
     * Make dynamic calls into the collection.
	 * 对集合进行动态调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->getCollection(), $method, $parameters);
    }

    /**
     * Render the contents of the paginator when casting to a string.
	 * 在转换为字符串时呈现分页器的内容
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }
}
