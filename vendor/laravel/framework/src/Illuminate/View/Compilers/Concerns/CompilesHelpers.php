<?php
/**
 * Illuminate，视图，编译器，问题，编译辅助
 */

namespace Illuminate\View\Compilers\Concerns;

use Illuminate\Foundation\Vite;

trait CompilesHelpers
{
    /**
     * Compile the CSRF statements into valid PHP.
	 * 将CSRF语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileCsrf()
    {
        return '<?php echo csrf_field(); ?>';
    }

    /**
     * Compile the "dd" statements into valid PHP.
	 * 将"dd"语句编译成有效的PHP
     *
     * @param  string  $arguments
     * @return string
     */
    protected function compileDd($arguments)
    {
        return "<?php dd{$arguments}; ?>";
    }

    /**
     * Compile the "dump" statements into valid PHP.
	 * 将"dump"语句编译成有效的PHP
     *
     * @param  string  $arguments
     * @return string
     */
    protected function compileDump($arguments)
    {
        return "<?php dump{$arguments}; ?>";
    }

    /**
     * Compile the method statements into valid PHP.
	 * 将方法语句编译成有效的PHP
     *
     * @param  string  $method
     * @return string
     */
    protected function compileMethod($method)
    {
        return "<?php echo method_field{$method}; ?>";
    }

    /**
     * Compile the "vite" statements into valid PHP.
	 * 将“vite”语句编译成有效的PHP
     *
     * @param  string|null  $arguments
     * @return string
     */
    protected function compileVite($arguments)
    {
        $arguments ??= '()';

        $class = Vite::class;

        return "<?php echo app('$class'){$arguments}; ?>";
    }

    /**
     * Compile the "viteReactRefresh" statements into valid PHP.
	 * 将"viteReactRefresh"语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileViteReactRefresh()
    {
        $class = Vite::class;

        return "<?php echo app('$class')->reactRefresh(); ?>";
    }
}
