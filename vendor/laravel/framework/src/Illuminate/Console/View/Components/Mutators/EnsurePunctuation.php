<?php
/**
 * Illuminate，控制台，视图，组件，函数，确保标点符号
 */

namespace Illuminate\Console\View\Components\Mutators;

class EnsurePunctuation
{
    /**
     * Ensures the given string ends with punctuation.
	 * 确保给定字符串以标点符号结束
     *
     * @param  string  $string
     * @return string
     */
    public function __invoke($string)
    {
        if (! str($string)->endsWith(['.', '?', '!', ':'])) {
            return "$string.";
        }

        return $string;
    }
}
