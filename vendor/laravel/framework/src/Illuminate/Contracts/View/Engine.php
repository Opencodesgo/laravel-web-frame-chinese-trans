<?php
/**
 * Illuminate，契约，视图，引擎
 */

namespace Illuminate\Contracts\View;

interface Engine
{
    /**
     * Get the evaluated contents of the view.
	 * 得到视图的求值内容
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = []);
}
