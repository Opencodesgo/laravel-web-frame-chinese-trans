<?php
/**
 * NunoMaduro，Collision，契约，适配器，Php单元，有可打印的测试用例名称
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Contracts\Adapters\Phpunit;

/**
 * @internal
 */
interface HasPrintableTestCaseName
{
    /**
     * Returns the test case name that should be used by the printer.
	 * 返回应该使用打印机使用的测试用例名称
     */
    public function getPrintableTestCaseName(): string;
}
