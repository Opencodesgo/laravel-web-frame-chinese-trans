<?php
/**
 * Illuminate，视图，编译器，问题，编译种类
 */

namespace Illuminate\View\Compilers\Concerns;

trait CompilesClasses
{
    /**
     * Compile the conditional class statement into valid PHP.
	 * 将条件类语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileClass($expression)
    {
        $expression = is_null($expression) ? '([])' : $expression;

        return "class=\"<?php echo \Illuminate\Support\Arr::toCssClasses{$expression} ?>\"";
    }
}
