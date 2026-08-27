<?php
/**
 * Illuminate，视图，编译器，问题，编译堆栈
 */

namespace Illuminate\View\Compilers\Concerns;

use Illuminate\Support\Str;

trait CompilesStacks
{
    /**
     * Compile the stack statements into the content.
	 * 将堆栈语句编译成内容
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileStack($expression)
    {
        return "<?php echo \$__env->yieldPushContent{$expression}; ?>";
    }

    /**
     * Compile the push statements into valid PHP.
	 * 将push语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compilePush($expression)
    {
        return "<?php \$__env->startPush{$expression}; ?>";
    }

    /**
     * Compile the push-once statements into valid PHP.
	 * 将push-once语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compilePushOnce($expression)
    {
        $parts = explode(',', $this->stripParentheses($expression), 2);

        [$stack, $id] = [$parts[0], $parts[1] ?? ''];

        $id = trim($id) ?: "'".(string) Str::uuid()."'";

        return '<?php if (! $__env->hasRenderedOnce('.$id.')): $__env->markAsRenderedOnce('.$id.');
$__env->startPush('.$stack.'); ?>';
    }

    /**
     * Compile the end-push statements into valid PHP.
	 * 将end-push语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndpush()
    {
        return '<?php $__env->stopPush(); ?>';
    }

    /**
     * Compile the end-push-once statements into valid PHP.
	 * 将end-push-once语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndpushOnce()
    {
        return '<?php $__env->stopPush(); endif; ?>';
    }

    /**
     * Compile the prepend statements into valid PHP.
	 * 将prepend语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compilePrepend($expression)
    {
        return "<?php \$__env->startPrepend{$expression}; ?>";
    }

    /**
     * Compile the prepend-once statements into valid PHP.
	 * 将前置一次语句编译成有效的PHP
     *
     * @param  string  $expression
     * @return string
     */
    protected function compilePrependOnce($expression)
    {
        $parts = explode(',', $this->stripParentheses($expression), 2);

        [$stack, $id] = [$parts[0], $parts[1] ?? ''];

        $id = trim($id) ?: "'".(string) Str::uuid()."'";

        return '<?php if (! $__env->hasRenderedOnce('.$id.')): $__env->markAsRenderedOnce('.$id.');
$__env->startPrepend('.$stack.'); ?>';
    }

    /**
     * Compile the end-prepend statements into valid PHP.
	 * 将end-prepend语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndprepend()
    {
        return '<?php $__env->stopPrepend(); ?>';
    }

    /**
     * Compile the end-prepend-once statements into valid PHP.
	 * 将end- prepend_once语句编译成有效的PHP
     *
     * @return string
     */
    protected function compileEndprependOnce()
    {
        return '<?php $__env->stopPrepend(); endif; ?>';
    }
}
