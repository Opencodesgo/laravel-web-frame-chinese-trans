<?php
/**
 * Illuminate，控制台，视图，组件，完成请求
 */

namespace Illuminate\Console\View\Components;

use Symfony\Component\Console\Question\Question;

class AskWithCompletion extends Component
{
    /**
     * Renders the component using the given arguments.
	 * 使用给定的参数呈现组件
     *
     * @param  string  $question
     * @param  array|callable  $choices
     * @param  string  $default
     * @return mixed
     */
    public function render($question, $choices, $default = null)
    {
        $question = new Question($question, $default);

        is_callable($choices)
            ? $question->setAutocompleterCallback($choices)
            : $question->setAutocompleterValues($choices);

        return $this->usingQuestionHelper(
            fn () => $this->output->askQuestion($question)
        );
    }
}
