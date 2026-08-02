<?php
/**
 * NunoMaduro，Collision，契约，参数格式化程序
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Contracts;

/**
 * @internal
 */
interface ArgumentFormatter
{
    /**
     * Formats the provided array of arguments into
     * an understandable description.
	 * 将提供的参数数组格式格式化为一个可以理解的描述
     */
    public function format(array $arguments, bool $recursive = true): string;
}
