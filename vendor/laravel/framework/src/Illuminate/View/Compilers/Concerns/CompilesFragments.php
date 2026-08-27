<?php
/**
 * Illuminate，视图，编译器，问题，编译片段
 */

namespace Illuminate\View\Compilers\Concerns;

trait CompilesFragments
{
    /**
     * The last compiled fragment.
	 * 最后编译的片段
     *
     * @var string
     */
    protected $lastFragment;

    /**
     * Compile the fragment statements into valid PHP.
	 * 将片段语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileFragment($expression)
    {
        $this->lastFragment = trim($expression, "()'\" ");

        return "<?php \$__env->startFragment{$expression}; ?>";
    }

    /**
     * Compile the end-fragment statements into valid PHP.
	 * 将片段结束语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndfragment()
    {
        return '<?php echo $__env->stopFragment(); ?>';
    }
}
