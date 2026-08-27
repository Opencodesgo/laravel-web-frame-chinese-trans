<?php
/**
 * Illuminate，视图，编译器，问题，编译授权
 */

namespace Illuminate\View\Compilers\Concerns;

trait CompilesStyles
{
    /**
     * Compile the conditional style statement into valid PHP.
	 * 将条件样式语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileStyle($expression)
    {
        $expression = is_null($expression) ? '([])' : $expression;

        return "style=\"<?php echo \Illuminate\Support\Arr::toCssStyles{$expression} ?>\"";
    }
}
