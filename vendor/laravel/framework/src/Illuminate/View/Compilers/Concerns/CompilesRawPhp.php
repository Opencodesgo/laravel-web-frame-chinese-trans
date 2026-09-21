<?php
/**
 * Illuminate，视图，编译器，问题，编译授权
 */

namespace Illuminate\View\Compilers\Concerns;

trait CompilesRawPhp
{
    /**
     * Compile the raw PHP statements into valid PHP.
	 * 将原始PHP语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compilePhp($expression)
    {
        if ($expression) {
            return "<?php {$expression}; ?>";
        }

        return '@php';
    }

    /**
     * Compile the unset statements into valid PHP.
	 * 将unset语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileUnset($expression)
    {
        return "<?php unset{$expression}; ?>";
    }
}
