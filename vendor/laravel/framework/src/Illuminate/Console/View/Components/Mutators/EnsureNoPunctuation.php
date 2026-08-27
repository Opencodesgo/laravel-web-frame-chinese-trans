<?php
/**
 * Illuminate，控制台，视图，组件，函数，确保没有标点符号
 */

namespace Illuminate\Console\View\Components\Mutators;

class EnsureNoPunctuation
{
    /**
     * Ensures the given string does not end with punctuation.
	 * 确保给定的字符串不以标点符号结束
     *
     * @param  string  $string
     * @return string
     */
    public function __invoke($string)
    {
        if (str($string)->endsWith(['.', '?', '!', ':'])) {
            return substr_replace($string, '', -1);
        }

        return $string;
    }
}
