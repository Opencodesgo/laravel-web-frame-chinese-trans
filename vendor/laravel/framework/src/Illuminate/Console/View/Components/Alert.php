<?php
/**
 * Illuminate，控制台，视图，组件，提示
 */

namespace Illuminate\Console\View\Components;

use Symfony\Component\Console\Output\OutputInterface;

class Alert extends Component
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
        $string = $this->mutate($string, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsurePunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $this->renderView('alert', [
            'content' => $string,
        ], $verbosity);
    }
}
