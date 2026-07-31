<?php
/**
 * Illuminate，契约，支持，可以转义转换为字符串时
 */

namespace Illuminate\Contracts\Support;

interface CanBeEscapedWhenCastToString
{
    /**
     * Indicate that the object's string representation should be escaped when __toString is invoked.
	 * 表明当__toString被调用时，对象的字符串表示应该被转义。
     *
     * @param  bool  $escape
     * @return $this
     */
    public function escapeWhenCastingToString($escape = true);
}
