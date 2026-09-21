<?php
/**
 * Illuminate，契约，基础，异常渲染器
 */

namespace Illuminate\Contracts\Foundation;

interface ExceptionRenderer
{
    /**
     * Renders the given exception as HTML.
	 * 将给定的异常呈现为HTML
     *
     * @param  \Throwable  $throwable
     * @return string
     */
    public function render($throwable);
}
