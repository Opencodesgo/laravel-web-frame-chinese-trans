<?php
/**
 * Termwind，组件，BreakLine
 */

declare(strict_types=1);

namespace Termwind\Components;

final class BreakLine extends Element
{
    /**
     * Get the string representation of the element.
	 * 获取元素的字符串表示形式
     */
    public function toString(): string
    {
        $display = $this->styles->getProperties()['styles']['display'] ?? 'inline';

        if ($display === 'hidden') {
            return '';
        }

        if ($display === 'block') {
            return parent::toString();
        }

        return parent::toString()."\r";
    }
}
