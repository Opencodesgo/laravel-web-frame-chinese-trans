<?php
/**
 * PhpParser，碰撞，契约，提供者
 */

/*
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Contracts;

/**
 * This is an Collision Provider contract.
 * 这是一个冲突提供程序契约。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
interface Provider
{
    /**
     * Registers the current Handler as Error Handler.
	 * 将当前处理程序注册为错误处理程序
     *
     * @return \NunoMaduro\Collision\Contracts\Provider
     */
    public function register(): Provider;

    /**
     * Returns the handler.
	 * 返回处理程序
     *
     * @return \NunoMaduro\Collision\Contracts\Handler
     */
    public function getHandler(): Handler;
}
