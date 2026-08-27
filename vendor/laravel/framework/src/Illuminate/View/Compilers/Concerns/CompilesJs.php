<?php
/**
 * Illuminate，视图，编译器，问题，编译 Js
 */

namespace Illuminate\View\Compilers\Concerns;

use Illuminate\Support\Js;

trait CompilesJs
{
    /**
     * Compile the "@js" directive into valid PHP.
	 * 将"@js"指令编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileJs(string $expression)
    {
        return sprintf(
            "<?php echo \%s::from(%s)->toHtml() ?>",
            Js::class, $this->stripParentheses($expression)
        );
    }
}
