<?php
/**
 * Symfony，Component，Console，格式化程序，输出格式化接口
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
	 * 设置装饰标记
     *
     * @return void
     */
    public function setDecorated(bool $decorated);

    /**
     * Whether the output will decorate messages.
	 * 输出是否会修饰消息
     */
    public function isDecorated(): bool;

    /**
     * Sets a new style.
	 * 设置一个新样式
     *
     * @return void
     */
    public function setStyle(string $name, OutputFormatterStyleInterface $style);

    /**
     * Checks if output formatter has style with specified name.
	 * 检查输出格式化程序是否具有指定名称的样式
     */
    public function hasStyle(string $name): bool;

    /**
     * Gets style options from style with specified name.
	 * 从具有指定名称的样式获取样式选项
     *
     * @throws \InvalidArgumentException When style isn't defined
     */
    public function getStyle(string $name): OutputFormatterStyleInterface;

    /**
     * Formats a message according to the given styles.
	 * 根据给定的样式格式化消息
     */
    public function format(?string $message): ?string;
}
