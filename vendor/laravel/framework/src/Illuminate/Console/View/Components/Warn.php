<?php
/**
 * Illuminate，控制台，视图，组件，警告
 */

namespace Illuminate\Console\View\Components;

use Symfony\Component\Console\Output\OutputInterface;

class Warn extends Component
{
    /**
     * Renders the component using the given arguments.
	 * 使用给定的参数呈现组件
     *
     * @param  string  $string
     * @param  int  $verbosity
     * @return void
     */
    public function render($string, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        with(new Line($this->output))
            ->render('warn', $string, $verbosity);
    }
}
