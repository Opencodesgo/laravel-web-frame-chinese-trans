<?php
/**
 * Psy，逐行读取，Readline
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Readline;

/**
 * An interface abstracting the various readline_* functions.
 * 抽象各种readline_*函数的接口。
 */
interface Readline
{
    /**
     * @param string|false $historyFile
     * @param int|null     $historySize
     * @param bool|null    $eraseDups
     */
    public function __construct($historyFile = null, $historySize = 0, $eraseDups = false);

    /**
     * Check whether this Readline class is supported by the current system.
	 * 检查当前系统是否支持此Readline类
     */
    public static function isSupported(): bool;

    /**
     * Check whether this Readline class supports bracketed paste.
	 * 一个基于libedit的Readline实现。
     */
    public static function supportsBracketedPaste(): bool;

    /**
     * Add a line to the command history.
	 * 在命令历史记录中添加一行
     *
     * @param string $line
     *
     * @return bool Success
     */
    public function addHistory(string $line): bool;

    /**
     * Clear the command history.
	 * 清除命令历史记录
     *
     * @return bool Success
     */
    public function clearHistory(): bool;

    /**
     * List the command history.
	 * 列出命令历史记录
     *
     * @return string[]
     */
    public function listHistory(): array;

    /**
     * Read the command history.
	 * 阅读命令历史
     *
     * @return bool Success
     */
    public function readHistory(): bool;

    /**
     * Read a single line of input from the user.
	 * 从用户那里读取一行输入
     *
     * @param string|null $prompt
     *
     * @return false|string
     */
    public function readline(?string $prompt = null);

    /**
     * Redraw readline to redraw the display.
	 * 重绘readline以重绘显示
     */
    public function redisplay();

    /**
     * Write the command history to a file.
	 * 将命令历史记录写入文件
     *
     * @return bool Success
     */
    public function writeHistory(): bool;
}
