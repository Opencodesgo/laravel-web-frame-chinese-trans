<?php
/**
 * NunoMaduro，Collision，契约，高光色
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Contracts;

/**
 * @internal
 */
interface Highlighter
{
    /**
     * Highlights the provided content.
	 * 突出显示所提供的内容
     */
    public function highlight(string $content, int $line): string;
}
