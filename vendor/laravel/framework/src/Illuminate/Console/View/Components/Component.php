<?php
/**
 * Illuminate，控制台，视图，组件，组件抽象类
 */

namespace Illuminate\Console\View\Components;

use Illuminate\Console\OutputStyle;
use Illuminate\Console\QuestionHelper;
use ReflectionClass;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;

use function Termwind\render;
use function Termwind\renderUsing;

abstract class Component
{
    /**
     * The output style implementation.
	 * 输出样式实现
     *
     * @var \Illuminate\Console\OutputStyle
     */
    protected $output;

    /**
     * The list of mutators to apply on the view data.
	 * 要应用于视图数据的变量列表
     *
     * @var array<int, callable(string): string>
     */
    protected $mutators;

    /**
     * Creates a new component instance.
	 * 创建一个新的组件实例
     *
     * @param  \Illuminate\Console\OutputStyle  $output
     * @return void
     */
    public function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Renders the given view.
	 * 渲染给定的视图
     *
     * @param  string  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  int  $verbosity
     * @return void
     */
    protected function renderView($view, $data, $verbosity)
    {
        renderUsing($this->output);

        render((string) $this->compile($view, $data), $verbosity);
    }

    /**
     * Compile the given view contents.
	 * 编译给定的视图内容
     *
     * @param  string  $view
     * @param  array  $data
     * @return void
     */
    protected function compile($view, $data)
    {
        extract($data);

        ob_start();

        include __DIR__."/../../resources/views/components/$view.php";

        return tap(ob_get_contents(), function () {
            ob_end_clean();
        });
    }

    /**
     * Mutates the given data with the given set of mutators.
	 * 用给定的一组变量对给定的数据进行变异
     *
     * @param  array<int, string>|string  $data
     * @param  array<int, callable(string): string>  $mutators
     * @return array<int, string>|string
     */
    protected function mutate($data, $mutators)
    {
        foreach ($mutators as $mutator) {
            $mutator = new $mutator;

            if (is_iterable($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = $mutator($value);
                }
            } else {
                $data = $mutator($data);
            }
        }

        return $data;
    }

    /**
     * Eventually performs a question using the component's question helper.
	 * 最终使用组件的问题帮助器执行一个问题
     *
     * @param  callable  $callable
     * @return mixed
     */
    protected function usingQuestionHelper($callable)
    {
        $property = with(new ReflectionClass(OutputStyle::class))
            ->getParentClass()
            ->getProperty('questionHelper');

        $property->setAccessible(true);

        $currentHelper = $property->isInitialized($this->output)
            ? $property->getValue($this->output)
            : new SymfonyQuestionHelper();

        $property->setValue($this->output, new QuestionHelper);

        try {
            return $callable();
        } finally {
            $property->setValue($this->output, $currentHelper);
        }
    }
}
