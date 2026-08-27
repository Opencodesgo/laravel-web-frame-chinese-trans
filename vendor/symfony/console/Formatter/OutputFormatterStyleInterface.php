<?php
/**
 * Symfony，Component，Console，格式化程序，输出格式化程序样式接口
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
 * Formatter style interface for defining styles.
 * 用于定义样式的格式化程序样式接口。
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface OutputFormatterStyleInterface
{
    /**
     * Sets style foreground color.
	 * 设置风格前景色
     *
     * @return void
     */
    public function setForeground(?string $color);

    /**
     * Sets style background color.
	 * 设置风格背景颜色
     *
     * @return void
     */
    public function setBackground(?string $color);

    /**
     * Sets some specific style option.
	 * 设置一些特定的样式选项
     *
     * @return void
     */
    public function setOption(string $option);

    /**
     * Unsets some specific style option.
	 * 取消设置某些特定的样式选项
     *
     * @return void
     */
    public function unsetOption(string $option);

    /**
     * Sets multiple style options at once.
	 * 一次设置多个样式选项
     *
     * @return void
     */
    public function setOptions(array $options);

    /**
     * Applies the style to a given text.
	 * 将样式应用于给定文本
     */
    public function apply(string $text): string;
}
