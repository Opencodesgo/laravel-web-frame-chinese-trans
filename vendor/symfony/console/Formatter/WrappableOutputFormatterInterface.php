<?php
/**
 * Symfony，Component，Console，格式化，可包装的输出格式化器接口
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
 * 用于支持自动换行的控制台输出的格式化程序接口
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
interface WrappableOutputFormatterInterface extends OutputFormatterInterface
{
    /**
     * Formats a message according to the given styles, wrapping at `$width` (0 means no wrapping).
	 * 根据给定的样式格式化消息，以‘ $width ’换行（0表示不换行）。
     */
    public function formatAndWrap(?string $message, int $width);
}
