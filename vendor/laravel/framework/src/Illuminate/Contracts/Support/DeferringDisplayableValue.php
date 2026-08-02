<?php
/**
 * Illuminate，契约，支持，延迟显示值
 */

namespace Illuminate\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
	 * 解析类要延迟的可显示值
     *
     * @return \Illuminate\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
