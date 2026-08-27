<?php
/**
 * Symfony，Component，ErrorHandler，错误渲染器，错误渲染器接口
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
 * 格式化一个异常以用作响应内容。
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
interface ErrorRendererInterface
{
    public const IDE_LINK_FORMATS = [
        'textmate' => 'txmt://open?url=file://%f&line=%l',
        'macvim' => 'mvim://open?url=file://%f&line=%l',
        'emacs' => 'emacs://open?url=file://%f&line=%l',
        'sublime' => 'subl://open?url=file://%f&line=%l',
        'phpstorm' => 'phpstorm://open?file=%f&line=%l',
        'atom' => 'atom://core/open/file?filename=%f&line=%l',
        'vscode' => 'vscode://file/%f:%l',
    ];

    /**
     * Renders a Throwable as a FlattenException.
	 * 将Throwable渲染为一个平坦异常
     */
    public function render(\Throwable $exception): FlattenException;
}
