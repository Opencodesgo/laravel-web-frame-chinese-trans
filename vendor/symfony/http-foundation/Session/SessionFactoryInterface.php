<?php
/**
 * Symfony，Component，HttpFoundation，Session，会话工厂接口
 */

/*
 * This file is part of the Symfony package.
 * 该文件是Symfony包的一部分
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface SessionFactoryInterface
{
    public function createSession(): SessionInterface;
}
