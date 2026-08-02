<?php
/**
 * PhpParser，碰撞，契约，适配器，单元测试，是否有可打印的测试用例名称
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

/**
 * @internal
 */
interface HasPrintableTestCaseName
{
    /**
     * Returns the test case name that should be used by the printer.
	 * 返回打印机应该使用的测试用例名称
     */
    public function getPrintableTestCaseName(): string;
}
