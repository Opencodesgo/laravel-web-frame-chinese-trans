<?php
/**
 * Illuminate，契约，分页，长度感知分页器
 */

namespace Illuminate\Contracts\Pagination;

interface LengthAwarePaginator extends Paginator
{
    /**
     * Create a range of pagination URLs.
	 * 创建一系列分页url
     *
     * @param  int  $start
     * @param  int  $end
     * @return array
     */
    public function getUrlRange($start, $end);

    /**
     * Determine the total number of items in the data store.
	 * 确定数据存储中的项目总数
     *
     * @return int
     */
    public function total();

    /**
     * Get the page number of the last available page.
	 * 获取最后可用页面的页码
     *
     * @return int
     */
    public function lastPage();
}
