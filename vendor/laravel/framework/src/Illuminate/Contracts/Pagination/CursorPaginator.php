<?php
/**
 * Illuminate，契约，分页，光标分页器
 */

namespace Illuminate\Contracts\Pagination;

interface CursorPaginator
{
    /**
     * Get the URL for a given cursor.
	 * 得到给定游标的URL
     *
     * @param  \Illuminate\Pagination\Cursor|null  $cursor
     * @return string
     */
    public function url($cursor);

    /**
     * Add a set of query string values to the paginator.
	 * 向分页器添加一组查询字符串值
     *
     * @param  array|string|null  $key
     * @param  string|null  $value
     * @return $this
     */
    public function appends($key, $value = null);

    /**
     * Get / set the URL fragment to be appended to URLs.
	 * 获取/设置要附加到URL的URL片段
     *
     * @param  string|null  $fragment
     * @return $this|string|null
     */
    public function fragment($fragment = null);

    /**
     * Get the URL for the previous page, or null.
	 * 获取前一页的URL，否则为空。
     *
     * @return string|null
     */
    public function previousPageUrl();

    /**
     * The URL for the next page, or null.
	 * 下一页的URL，或者为空。
     *
     * @return string|null
     */
    public function nextPageUrl();

    /**
     * Get all of the items being paginated.
	 * 获取所有被分页的项
     *
     * @return array
     */
    public function items();

    /**
     * Get the "cursor" of the previous set of items.
	 * 获取前一组项的"游标"
     *
     * @return \Illuminate\Pagination\Cursor|null
     */
    public function previousCursor();

    /**
     * Get the "cursor" of the next set of items.
	 * 获取下一组项目的"光标"
     *
     * @return \Illuminate\Pagination\Cursor|null
     */
    public function nextCursor();

    /**
     * Determine how many items are being shown per page.
	 * 确定每页显示多少项
     *
     * @return int
     */
    public function perPage();

    /**
     * Get the current cursor being paginated.
	 * 获取正在分页的当前游标
     *
     * @return \Illuminate\Pagination\Cursor|null
     */
    public function cursor();

    /**
     * Determine if there are enough items to split into multiple pages.
	 * 确定是否有足够的项目可以拆分为多个页面
     *
     * @return bool
     */
    public function hasPages();

    /**
     * Get the base path for paginator generated URLs.
	 * 获取分页器生成的url的基本路径
     *
     * @return string|null
     */
    public function path();

    /**
     * Determine if the list of items is empty or not.
	 * 确定项目列表是否为空
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Determine if the list of items is not empty.
	 * 确定项目列表是否为空
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Render the paginator using a given view.
	 * 使用给定视图呈现分页器
     *
     * @param  string|null  $view
     * @param  array  $data
     * @return string
     */
    public function render($view = null, $data = []);
}
