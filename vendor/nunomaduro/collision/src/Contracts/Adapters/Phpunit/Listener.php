<?php
/**
 * PhpParser，碰撞，契约，适配器，单元测试，监听器
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Contracts\Adapters\Phpunit;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;

/**
 * This is an Collision Phpunit Adapter contract.
 * 这是一个冲突Phpunit适配器契约。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
interface Listener extends TestListener
{
    /**
     * Renders the provided error
     * on the console.
	 * 在控制台显示提供的错误信息
     *
     * @return void
     */
    public function render(Test $test, \Throwable $t);
}
