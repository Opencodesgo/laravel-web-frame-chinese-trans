<?php
/**
 * Symfony，Component，Console，格式化程序，输出格式化程序样式堆栈
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Formatter;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class OutputFormatterStyleStack implements ResetInterface
{
    /**
     * @var OutputFormatterStyleInterface[]
     */
    private array $styles = [];

    private OutputFormatterStyleInterface $emptyStyle;

    public function __construct(?OutputFormatterStyleInterface $emptyStyle = null)
    {
        $this->emptyStyle = $emptyStyle ?? new OutputFormatterStyle();
        $this->reset();
    }

    /**
     * Resets stack (ie. empty internal arrays).
     *
     * @return void
     */
    public function reset()
    {
        $this->styles = [];
    }

    /**
     * Pushes a style in the stack.
	 * 将样式推入堆栈
     *
     * @return void
     */
    public function push(OutputFormatterStyleInterface $style)
    {
        $this->styles[] = $style;
    }

    /**
     * Pops a style from the stack.
	 * 从堆栈中弹出一个样式
     *
     * @throws InvalidArgumentException When style tags incorrectly nested
     */
    public function pop(?OutputFormatterStyleInterface $style = null): OutputFormatterStyleInterface
    {
        if (!$this->styles) {
            return $this->emptyStyle;
        }

        if (null === $style) {
            return array_pop($this->styles);
        }

        foreach (array_reverse($this->styles, true) as $index => $stackedStyle) {
            if ($style->apply('') === $stackedStyle->apply('')) {
                $this->styles = \array_slice($this->styles, 0, $index);

                return $stackedStyle;
            }
        }

        throw new InvalidArgumentException('Incorrectly nested style tag found.');
    }

    /**
     * Computes current style with stacks top codes.
	 * 使用堆栈顶部代码计算当前样式
     */
    public function getCurrent(): OutputFormatterStyleInterface
    {
        if (!$this->styles) {
            return $this->emptyStyle;
        }

        return $this->styles[\count($this->styles) - 1];
    }

    /**
     * @return $this
     */
    public function setEmptyStyle(OutputFormatterStyleInterface $emptyStyle): static
    {
        $this->emptyStyle = $emptyStyle;

        return $this;
    }

    public function getEmptyStyle(): OutputFormatterStyleInterface
    {
        return $this->emptyStyle;
    }
}
