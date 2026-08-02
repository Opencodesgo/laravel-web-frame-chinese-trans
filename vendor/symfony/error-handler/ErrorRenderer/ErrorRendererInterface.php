<?php
/**
 * Symfony，Component，ErrorHandler，错误渲染器，错误渲染接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ErrorHandler\ErrorRenderer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;

/**
 * Formats an exception to be used as response content.
 * 格式一个例外,用于响应内容。
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
interface ErrorRendererInterface
{
    /**
     * Renders a Throwable as a FlattenException.
	 * 把一个抛掷变成一个平坦的异常
     */
    public function render(\Throwable $exception): FlattenException;
}
