<?php
/**
 * PhpParser，碰撞，适配器，单元测试，打印
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Adapters\Phpunit;

/*
 * This `if` condition exists because phpunit
 * is not a direct dependency of Collision.
 * 这个 `if` 条件存在是因为 phpunit 并不是 Collision 的直接依赖。
 *
 * This code bellow it's for phpunit@8
 */
if (class_exists(\PHPUnit\Runner\Version::class) && intval(substr(\PHPUnit\Runner\Version::id(), 0, 1)) === 8) {
    /**
     * This is an Collision Phpunit Adapter implementation.
	 * 这是一个碰撞Phpunit适配器实现
     *
     * @internal
     */
    final class Printer extends \PHPUnit\Util\Printer implements \PHPUnit\Framework\TestListener
    {
        use PrinterContents;
    }
}

/*
 * This `if` condition exists because phpunit
 * is not a direct dependency of Collision.
 * 这个 `if` 条件存在是因为 phpunit 并不是 Collision 的直接依赖。
 *
 * This code bellow it's for phpunit@9
 */
if (class_exists(\PHPUnit\Runner\Version::class) && intval(substr(\PHPUnit\Runner\Version::id(), 0, 1)) === 9) {
    /**
     * This is an Collision Phpunit Adapter implementation.
	 * 这是一个碰撞Phpunit适配器实现
     *
     * @internal
     */
    final class Printer implements \PHPUnit\TextUI\ResultPrinter
    {
        use PrinterContents;

        /**
         * Intentionally left blank as we output things on events of the listener.
		 * 当我们输出关于侦听器事件的内容时，故意保留空白。
         */
        public function printResult(\PHPUnit\Framework\TestResult $result): void
        {
            // ..
        }
    }
}
