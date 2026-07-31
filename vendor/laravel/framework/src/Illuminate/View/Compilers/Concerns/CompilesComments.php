<?php
/**
 * Illuminate，视图，编译，问题，编译注释
 */

namespace Illuminate\View\Compilers\Concerns;

trait CompilesComments
{
    /**
     * Compile Blade comments into an empty string.
	 * 将Blade注释编译成一个空字符串
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComments($value)
    {
        $pattern = sprintf('/%s--(.*?)--%s/s', $this->contentTags[0], $this->contentTags[1]);

        return preg_replace($pattern, '', $value);
    }
}
