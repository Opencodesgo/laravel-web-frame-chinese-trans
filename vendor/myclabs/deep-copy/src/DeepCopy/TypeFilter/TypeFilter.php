<?php
/**
 * DeepCopy，类型过滤器，类型过滤器
 */

namespace DeepCopy\TypeFilter;

interface TypeFilter
{
    /**
     * Applies the filter to the object.
	 * 将过滤器应用于对象
     *
     * @param mixed $element
     */
    public function apply($element);
}
