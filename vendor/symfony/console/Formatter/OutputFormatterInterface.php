<?php
/**
 * Symfony，Component，Console，格式化程序，输出格式化程序接口
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

/**
 * Formatter interface for console output.
 * 用于控制台输出的格式化程序接口。
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OutputFormatterInterface
{
    /**
     * Sets the decorated flag.
	 * 设置装饰的标志
     */
    public function setDecorated(bool $decorated);

    /**
     * Whether the output will decorate messages.
	 * 输出是否会装饰信息
     *
     * @return bool
     */
    public function isDecorated();

    /**
     * Sets a new style.
	 * 设置一种新的风格
     */
    public function setStyle(string $name, OutputFormatterStyleInterface $style);

    /**
     * Checks if output formatter has style with specified name.
	 * 检查输出格式化程序是否有指定名称的样式
     *
     * @return bool
     */
    public function hasStyle(string $name);

    /**
     * Gets style options from style with specified name.
	 * 以指定的名称获取样式选项
     *
     * @return OutputFormatterStyleInterface
     *
     * @throws \InvalidArgumentException When style isn't defined
     */
    public function getStyle(string $name);

    /**
     * Formats a message according to the given styles.
	 * 根据给定的样式格式化消息
     *
     * @return string|null
     */
    public function format(?string $message);
}
