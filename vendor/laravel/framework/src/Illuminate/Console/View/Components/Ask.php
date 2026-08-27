<?php
/**
 * Illuminate，控制台，视图，组件，请求
 */

namespace Illuminate\Console\View\Components;

class Ask extends Component
{
    /**
     * Renders the component using the given arguments.
	 * 使用给定的参数呈现组件
     *
     * @param  string  $question
     * @param  string  $default
     * @return mixed
     */
    public function render($question, $default = null)
    {
        return $this->usingQuestionHelper(fn () => $this->output->ask($question, $default));
    }
}
