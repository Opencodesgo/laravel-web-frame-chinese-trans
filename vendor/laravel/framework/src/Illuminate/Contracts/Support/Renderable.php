<?php
/**
 * Illuminate，契约，支持，可渲染的
 */

namespace Illuminate\Contracts\Support;

interface Renderable
{
    /**
     * Get the evaluated contents of the object.
	 * 获取对象的求值内容
     *
     * @return string
     */
    public function render();
}
