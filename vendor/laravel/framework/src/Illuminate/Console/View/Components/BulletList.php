<?php
/**
 * Illuminate，控制台，视图，组件，符号列表
 */

namespace Illuminate\Console\View\Components;

use Symfony\Component\Console\Output\OutputInterface;

class BulletList extends Component
{
    /**
     * Renders the component using the given arguments.
	 * 使用给定的参数呈现组件
     *
     * @param  array<int, string>  $elements
     * @param  int  $verbosity
     * @return void
     */
    public function render($elements, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $elements = $this->mutate($elements, [
            Mutators\EnsureDynamicContentIsHighlighted::class,
            Mutators\EnsureNoPunctuation::class,
            Mutators\EnsureRelativePaths::class,
        ]);

        $this->renderView('bullet-list', [
            'elements' => $elements,
        ], $verbosity);
    }
}
