<?php
/**
 * Illuminate，契约，视图，视图
 */

namespace Illuminate\Contracts\View;

use Illuminate\Contracts\Support\Renderable;

interface View extends Renderable
{
    /**
     * Get the name of the view.
	 * 获取视图的名称
     *
     * @return string
     */
    public function name();

    /**
     * Add a piece of data to the view.
	 * 向视图添加一段数据
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null);

    /**
     * Get the array of view data.
	 * 获取视图数据数组
     *
     * @return array
     */
    public function getData();
}
