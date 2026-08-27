<?php
/**
 * Termwind，终端
 */

declare(strict_types=1);

namespace Termwind;

use Symfony\Component\Console\Terminal as ConsoleTerminal;

/**
 * @internal
 */
final class Terminal
{
    /**
     * An instance of Symfony's console terminal.
	 * Symfony控制台终端的一个实例
     */
    private ConsoleTerminal $terminal;

    /**
     * Creates a new terminal instance.
	 * 创建一个新的终端实例
     */
    public function __construct(?ConsoleTerminal $terminal = null)
    {
        $this->terminal = $terminal ?? new ConsoleTerminal;
    }

    /**
     * Gets the terminal width.
	 * 获取终端宽度
     */
    public function width(): int
    {
        return $this->terminal->getWidth();
    }

    /**
     * Gets the terminal height.
	 * 获取终端高度
     */
    public function height(): int
    {
        return $this->terminal->getHeight();
    }

    /**
     * Clears the terminal screen.
	 * 清除终端屏幕
     */
    public function clear(): void
    {
        Termwind::getRenderer()->write("\ec");
    }
}
