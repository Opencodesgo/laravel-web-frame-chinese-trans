<?php
/**
 * Illuminate，分页，游标分页器
 */

namespace Illuminate\Pagination;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Pagination\CursorPaginator as PaginatorContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

class CursorPaginator extends AbstractCursorPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, JsonSerializable, PaginatorContract
{
    /**
     * Indicates whether there are more items in the data source.
	 * 指示数据源中是否有更多项
     *
     * @return bool
     */
    protected $hasMore;

    /**
     * Create a new paginator instance.
	 * 创建一个新的分页器实例
     *
     * @param  mixed  $items
     * @param  int  $perPage
     * @param  \Illuminate\Pagination\Cursor|null  $cursor
     * @param  array  $options  (path, query, fragment, pageName)
     * @return void
     */
    public function __construct($items, $perPage, $cursor = null, array $options = [])
    {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }

        $this->perPage = (int) $perPage;
        $this->cursor = $cursor;
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;

        $this->setItems($items);
    }

    /**
     * Set the items for the paginator.
	 * 设置分页器的项
     *
     * @param  mixed  $items
     * @return void
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = $this->items->count() > $this->perPage;

        $this->items = $this->items->slice(0, $this->perPage);

        if (! is_null($this->cursor) && $this->cursor->pointsToPreviousItems()) {
            $this->items = $this->items->reverse()->values();
        }
    }

    /**
     * Render the paginator using the given view.
	 * 使用给定视图呈现分页器
     *
     * @param  string|null  $view
     * @param  array  $data
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function links($view = null, $data = [])
    {
        return $this->render($view, $data);
    }

    /**
     * Render the paginator using the given view.
	 * 使用给定视图呈现分页器
     *
     * @param  string|null  $view
     * @param  array  $data
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function render($view = null, $data = [])
    {
        return static::viewFactory()->make($view ?: Paginator::$defaultSimpleView, array_merge($data, [
            'paginator' => $this,
        ]));
    }

    /**
     * Determine if there are more items in the data source.
	 * 确定数据源中是否有更多项
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return (is_null($this->cursor) && $this->hasMore) ||
            (! is_null($this->cursor) && $this->cursor->pointsToNextItems() && $this->hasMore) ||
            (! is_null($this->cursor) && $this->cursor->pointsToPreviousItems());
    }

    /**
     * Determine if there are enough items to split into multiple pages.
	 * 确定是否有足够的项目可以拆分为多个页面
     *
     * @return bool
     */
    public function hasPages()
    {
        return ! $this->onFirstPage() || $this->hasMorePages();
    }

    /**
     * Determine if the paginator is on the first page.
	 * 确定分页器是否在第一页上
     *
     * @return bool
     */
    public function onFirstPage()
    {
        return is_null($this->cursor) || ($this->cursor->pointsToPreviousItems() && ! $this->hasMore);
    }

    /**
     * Determine if the paginator is on the last page.
	 * 确定分页器是否在最后一页上
     *
     * @return bool
     */
    public function onLastPage()
    {
        return ! $this->hasMorePages();
    }

    /**
     * Get the instance as an array.
	 * 以数组的形式获取实例
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'data' => $this->items->toArray(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'next_cursor' => $this->nextCursor()?->encode(),
            'next_page_url' => $this->nextPageUrl(),
            'prev_cursor' => $this->previousCursor()?->encode(),
            'prev_page_url' => $this->previousPageUrl(),
        ];
    }

    /**
     * Convert the object into something JSON serializable.
	 * 将对象转换为JSON可序列化的对象
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
	 * 将对象转换为其JSON表示形式
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
