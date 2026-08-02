<?php
/**
 * Illuminate，视图，编译器，问题，编译条件
 */

namespace Illuminate\View\Compilers\Concerns;

use Illuminate\Support\Str;

trait CompilesConditionals
{
    /**
     * Identifier for the first case in switch statement.
	 * switch语句中第一个case的标识符
     *
     * @var bool
     */
    protected $firstCaseInSwitch = true;

    /**
     * Compile the if-auth statements into valid PHP.
	 * 将if-auth语句编译成有效的PHP
     *
     * @param  string|null  $guard
     * @return string
     */
    protected function compileAuth($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php if(auth()->guard{$guard}->check()): ?>";
    }

    /**
     * Compile the else-auth statements into valid PHP.
	 * 将else-auth语句编译成有效的PHP
     *
     * @param  string|null  $guard
     * @return string
     */
    protected function compileElseAuth($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php elseif(auth()->guard{$guard}->check()): ?>";
    }

    /**
     * Compile the end-auth statements into valid PHP.
	 * 将end-auth语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndAuth()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the env statements into valid PHP.
	 * 将env语句编译成有效的PHP
     *
     * @param  string  $environments
     * @return string
     */
    protected function compileEnv($environments)
    {
        return "<?php if(app()->environment{$environments}): ?>";
    }

    /**
     * Compile the end-env statements into valid PHP.
	 * 将end-env语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndEnv()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the production statements into valid PHP.
	 * 将生成语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileProduction()
    {
        return "<?php if(app()->environment('production')): ?>";
    }

    /**
     * Compile the end-production statements into valid PHP.
	 * 将最终生成的语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndProduction()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the if-guest statements into valid PHP.
	 * 将if-guest语句编译成有效的PHP
     *
     * @param  string|null  $guard
     * @return string
     */
    protected function compileGuest($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php if(auth()->guard{$guard}->guest()): ?>";
    }

    /**
     * Compile the else-guest statements into valid PHP.
	 * 将else-guest语句编译成有效的PHP
     *
     * @param  string|null  $guard
     * @return string
     */
    protected function compileElseGuest($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php elseif(auth()->guard{$guard}->guest()): ?>";
    }

    /**
     * Compile the end-guest statements into valid PHP.
	 * 将end-guest语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndGuest()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the has-section statements into valid PHP.
	 * 将has-section语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileHasSection($expression)
    {
        return "<?php if (! empty(trim(\$__env->yieldContent{$expression}))): ?>";
    }

    /**
     * Compile the section-missing statements into valid PHP.
	 * 将缺少节的语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileSectionMissing($expression)
    {
        return "<?php if (empty(trim(\$__env->yieldContent{$expression}))): ?>";
    }

    /**
     * Compile the if statements into valid PHP.
	 * 将if语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compile the unless statements into valid PHP.
	 * 将unless语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileUnless($expression)
    {
        return "<?php if (! {$expression}): ?>";
    }

    /**
     * Compile the else-if statements into valid PHP.
	 * 将else-if语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compile the else statements into valid PHP.
	 * 将else语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileElse()
    {
        return '<?php else: ?>';
    }

    /**
     * Compile the end-if statements into valid PHP.
	 * 将end-if语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndif()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the end-unless statements into valid PHP.
	 * 将end-unless语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndunless()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the if-isset statements into valid PHP.
	 * 将if-isset语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileIsset($expression)
    {
        return "<?php if(isset{$expression}): ?>";
    }

    /**
     * Compile the end-isset statements into valid PHP.
	 * 将end-isset语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndIsset()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the switch statements into valid PHP.
	 * 将switch语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileSwitch($expression)
    {
        $this->firstCaseInSwitch = true;

        return "<?php switch{$expression}:";
    }

    /**
     * Compile the case statements into valid PHP.
	 * 将case语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileCase($expression)
    {
        if ($this->firstCaseInSwitch) {
            $this->firstCaseInSwitch = false;

            return "case {$expression}: ?>";
        }

        return "<?php case {$expression}: ?>";
    }

    /**
     * Compile the default statements in switch case into valid PHP.
	 * 将switch情况下的默认语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileDefault()
    {
        return '<?php default: ?>';
    }

    /**
     * Compile the end switch statements into valid PHP.
	 * 将结束开关语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndSwitch()
    {
        return '<?php endswitch; ?>';
    }

    /**
     * Compile an once block into valid PHP.
	 * 将once块编译成有效的PHP
     *
     * @return string
     */
    protected function compileOnce($id = null)
    {
        $id = $id ? $this->stripParentheses($id) : "'".(string) Str::uuid()."'";

        return '<?php if (! $__env->hasRenderedOnce('.$id.')): $__env->markAsRenderedOnce('.$id.'); ?>';
    }

    /**
     * Compile an end-once block into valid PHP.
	 * 将一个end-once块编译成有效的PHP
     *
     * @return string
     */
    public function compileEndOnce()
    {
        return '<?php endif; ?>';
    }
}
