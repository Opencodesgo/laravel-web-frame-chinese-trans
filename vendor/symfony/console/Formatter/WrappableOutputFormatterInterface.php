<?php
/**
 * Symfony，Component，Console，格式化程序，封装的输出格式化程序接口
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
 * Formatter interface for console output that supports word wrapping.
 * 支持word包装的控制台输出的格式化程序接口。
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface WrappableOutputFormatterInterface extends OutputFormatterInterface
{
    /**
     * Formats a message according to the given styles, wrapping at `$width` (0 means no wrapping).
	 * 根据给定的样式格式化消息,包装在“$ width”(0表示没有包装)。
     */
    public function formatAndWrap(?string $message, int $width);
}
