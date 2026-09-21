<?php
/**
 * Illuminate，控制台，视图，组件，确认
 */

namespace Illuminate\Console\View\Components;

class Confirm extends Component
{
    /**
     * Renders the component using the given arguments.
	 * 使用给定的参数呈现组件
     *
     * @param  string  $question
     * @param  bool  $default
     * @return bool
     */
    public function render($question, $default = false)
    {
        return $this->usingQuestionHelper(
            fn () => $this->output->confirm($question, $default),
        );
    }
}
