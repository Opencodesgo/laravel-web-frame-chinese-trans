<?php
/**
 * Illuminate，控制台，视图，组件，函数，确保相对路径
 */

namespace Illuminate\Console\View\Components\Mutators;

class EnsureRelativePaths
{
    /**
     * Ensures the given string only contains relative paths.
	 * 确保给定的字符串只包含相对路径
     *
     * @param  string  $string
     * @return string
     */
    public function __invoke($string)
    {
        if (function_exists('app') && app()->has('path.base')) {
            $string = str_replace(base_path().'/', '', $string);
        }

        return $string;
    }
}
