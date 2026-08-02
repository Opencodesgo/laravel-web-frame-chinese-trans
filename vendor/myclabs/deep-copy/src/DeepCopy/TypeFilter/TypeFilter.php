<?php
/**
 * DeepCopy，类型过滤器，Type Filter
 */

namespace DeepCopy\TypeFilter;

interface TypeFilter
{
    /**
     * Applies the filter to the object.
	 * 将筛选器应用于对象
     *
     * @param mixed $element
     */
    public function apply($element);
}
