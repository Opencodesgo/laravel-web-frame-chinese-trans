<?php
/**
 * Symfony，Component，Console，输出，输出接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;

/**
 * OutputInterface is the interface implemented by all Output classes.
 * OutputInterface 是由所有Output类实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface OutputInterface
{
    public const VERBOSITY_QUIET = 16;
    public const VERBOSITY_NORMAL = 32;
    public const VERBOSITY_VERBOSE = 64;
    public const VERBOSITY_VERY_VERBOSE = 128;
    public const VERBOSITY_DEBUG = 256;

    public const OUTPUT_NORMAL = 1;
    public const OUTPUT_RAW = 2;
    public const OUTPUT_PLAIN = 4;

    /**
     * Writes a message to the output.
	 * 将消息写入输出。
     *
     * @param bool $newline Whether to add a newline
     * @param int  $options A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                      0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return void
     */
    public function write(string|iterable $messages, bool $newline = false, int $options = 0);

    /**
     * Writes a message to the output and adds a newline at the end.
	 * 将消息写入输出并在末尾添加换行符
     *
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                     0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return void
     */
    public function writeln(string|iterable $messages, int $options = 0);

    /**
     * Sets the verbosity of the output.
	 * 设置输出的详细程度
     *
     * @param self::VERBOSITY_* $level
     *
     * @return void
     */
    public function setVerbosity(int $level);

    /**
     * Gets the current verbosity of the output.
	 * 获取当前输出的详细信息
     *
     * @return self::VERBOSITY_*
     */
    public function getVerbosity(): int;

    /**
     * Returns whether verbosity is quiet (-q).
	 * 返回verbose是否为quiet （-q）
     */
    public function isQuiet(): bool;

    /**
     * Returns whether verbosity is verbose (-v).
     */
    public function isVerbose(): bool;

    /**
     * Returns whether verbosity is very verbose (-vv).
     */
    public function isVeryVerbose(): bool;

    /**
     * Returns whether verbosity is debug (-vvv).
     */
    public function isDebug(): bool;

    /**
     * Sets the decorated flag.
	 * 设置装饰标识
     *
     * @return void
     */
    public function setDecorated(bool $decorated);

    /**
     * Gets the decorated flag.
	 * 获得装饰的标识
     */
    public function isDecorated(): bool;

    /**
     * @return void
     */
    public function setFormatter(OutputFormatterInterface $formatter);

    /**
     * Returns current output formatter instance.
	 * 返回当前输出格式化程序实例
     */
    public function getFormatter(): OutputFormatterInterface;
}
