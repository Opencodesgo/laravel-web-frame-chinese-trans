<?php
/**
 * Illuminate，控制台，视图，组件，选择
 */

namespace Illuminate\Console\View\Components;

use Symfony\Component\Console\Question\ChoiceQuestion;

class Choice extends Component
{
    /**
     * Renders the component using the given arguments.
	 * 使用给定的参数呈现组件
     *
     * @param  string  $question
     * @param  array<array-key, string>  $choices
     * @param  mixed  $default
     * @param  int  $attempts
     * @param  bool  $multiple
     * @return mixed
     */
    public function render($question, $choices, $default = null, $attempts = null, $multiple = false)
    {
        return $this->usingQuestionHelper(
            fn () => $this->output->askQuestion(
                (new ChoiceQuestion($question, $choices, $default))
                    ->setMaxAttempts($attempts)
                    ->setMultiselect($multiple)
            ),
        );
    }
}
