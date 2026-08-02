<?php
/**
 * Illuminate，契约，支持，Html能力
 */

namespace Illuminate\Contracts\Support;

interface Htmlable
{
    /**
     * Get content as a string of HTML.
	 * 获取HTML字符串形式的内容
     *
     * @return string
     */
    public function toHtml();
}
