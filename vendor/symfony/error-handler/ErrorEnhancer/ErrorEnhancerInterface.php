<?php
/**
 * Symfony，Component，ErrorHandler，错误增强，错误增强接口
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
	 * 如果类能够改善错误，则返回一个\Throwable实例，否则返回null。
     */
    public function enhance(\Throwable $error): ?\Throwable;
}
