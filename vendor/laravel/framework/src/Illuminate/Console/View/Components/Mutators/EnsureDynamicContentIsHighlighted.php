<?php
/**
 * Illuminate，控制台，视图，组件，函数，确保动态内容突出显示
 */

namespace Illuminate\Console\View\Components\Mutators;

class EnsureDynamicContentIsHighlighted
{
    /**
     * Highlight dynamic content within the given string.
	 * 突出显示给定字符串中的动态内容
     *
     * @param  string  $string
     * @return string
     */
    public function __invoke($string)
    {
        return preg_replace('/\[([^\]]+)\]/', '<options=bold>[$1]</>', (string) $string);
    }
}
