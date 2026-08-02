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
 * OutputInterface是所有输出类实现的接口。
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
	 * 向输出写入消息
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param bool            $newline  Whether to add a newline
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function write($messages, bool $newline = false, int $options = 0);

    /**
     * Writes a message to the output and adds a newline at the end.
	 * 设置输出的verbosity
     *
     * @param string|iterable $messages The message as an iterable of strings or a single string
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function writeln($messages, int $options = 0);

    /**
     * Sets the verbosity of the output.
	 * 设置输出的verbosity
     */
    public function setVerbosity(int $level);

    /**
     * Gets the current verbosity of the output.
	 * 得到当前输出的verbosity
     *
     * @return int
     */
    public function getVerbosity();

    /**
     * Returns whether verbosity is quiet (-q).
	 * 返回verbose是否为quiet （-q）
     *
     * @return bool
     */
    public function isQuiet();

    /**
     * Returns whether verbosity is verbose (-v).
	 * 返回verbose是否为verbose （-v）
     *
     * @return bool
     */
    public function isVerbose();

    /**
     * Returns whether verbosity is very verbose (-vv).
	 * 返回verbose是否非常verbose （-vv）
     *
     * @return bool
     */
    public function isVeryVerbose();

    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool
     */
    public function isDebug();

    /**
     * Sets the decorated flag.
	 * 设置装饰的标识
     */
    public function setDecorated(bool $decorated);

    /**
     * Gets the decorated flag.
	 * 得到装饰的标识
     *
     * @return bool
     */
    public function isDecorated();

    public function setFormatter(OutputFormatterInterface $formatter);

    /**
     * Returns current output formatter instance.
	 * 返回当前输出格式化程序实例
     *
     * @return OutputFormatterInterface
     */
    public function getFormatter();
}
