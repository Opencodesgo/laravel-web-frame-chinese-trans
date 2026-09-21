<?php
/**
 * Illuminate，视图，编译器，问题，编译 Echo
 */

namespace Illuminate\View\Compilers\Concerns;

use Closure;
use Illuminate\Support\Str;

trait CompilesEchos
{
    /**
     * Custom rendering callbacks for stringable objects.
	 * 可字符串对象的自定义呈现回调
     *
     * @var array
     */
    protected $echoHandlers = [];

    /**
     * Add a handler to be executed before echoing a given class.
	 * 在回显给定类之前添加要执行的处理程序
     *
     * @param  string|callable  $class
     * @param  callable|null  $handler
     * @return void
     */
    public function stringable($class, $handler = null)
    {
        if ($class instanceof Closure) {
            [$class, $handler] = [$this->firstClosureParameterType($class), $class];
        }

        $this->echoHandlers[$class] = $handler;
    }

    /**
     * Compile Blade echos into valid PHP.
	 * 将Blade回显编译成有效的PHP
     *
     * @param  string  $value
     * @return string
     */
    public function compileEchos($value)
    {
        foreach ($this->getEchoMethods() as $method) {
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     * Get the echo methods in the proper order for compilation.
	 * 以适当的顺序获取echo方法以进行编译
     *
     * @return array
     */
    protected function getEchoMethods()
    {
        return [
            'compileRawEchos',
            'compileEscapedEchos',
            'compileRegularEchos',
        ];
    }

    /**
     * Compile the "raw" echo statements.
	 * 编译“原始”echo语句
     *
     * @param  string  $value
     * @return string
     */
    protected function compileRawEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            return $matches[1]
                ? substr($matches[0], 1)
                : "<?php echo {$this->wrapInEchoHandler($matches[2])}; ?>{$whitespace}";
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the "regular" echo statements.
	 * 编译“常规”echo语句
     *
     * @param  string  $value
     * @return string
     */
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            $wrapped = sprintf($this->echoFormat, $this->wrapInEchoHandler($matches[2]));

            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$wrapped}; ?>{$whitespace}";
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the escaped echo statements.
	 * 编译转义的echo语句
     *
     * @param  string  $value
     * @return string
     */
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            return $matches[1]
                ? $matches[0]
                : "<?php echo e({$this->wrapInEchoHandler($matches[2])}); ?>{$whitespace}";
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Add an instance of the blade echo handler to the start of the compiled string.
	 * 将blade回显处理程序的实例添加到编译字符串的开头
     *
     * @param  string  $result
     * @return string
     */
    protected function addBladeCompilerVariable($result)
    {
        return "<?php \$__bladeCompiler = app('blade.compiler'); ?>".$result;
    }

    /**
     * Wrap the echoable value in an echo handler if applicable.
	 * 如果适用，将可回显值包装在回显处理程序中。
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapInEchoHandler($value)
    {
        $value = Str::of($value)
            ->trim()
            ->when(str_ends_with($value, ';'), function ($str) {
                return $str->beforeLast(';');
            });

        return empty($this->echoHandlers) ? $value : '$__bladeCompiler->applyEchoHandler('.$value.')';
    }

    /**
     * Apply the echo handler for the value if it exists.
	 * 如果该值存在，则应用该值的回显处理程序。
     *
     * @param  string  $value
     * @return string
     */
    public function applyEchoHandler($value)
    {
        if (is_object($value) && isset($this->echoHandlers[get_class($value)])) {
            return call_user_func($this->echoHandlers[get_class($value)], $value);
        }

        return $value;
    }
}
