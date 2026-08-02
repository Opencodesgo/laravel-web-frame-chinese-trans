<?php
/**
 * Symfony，Component，ErrorHandler，错误增强器，误差增强接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ErrorHandler\ErrorEnhancer;

interface ErrorEnhancerInterface
{
    /**
     * Returns an \Throwable instance if the class is able to improve the error, null otherwise.
	 * 如果类能够改进错误,否则返回一个\抛掷实例。
     */
    public function enhance(\Throwable $error): ?\Throwable;
}
