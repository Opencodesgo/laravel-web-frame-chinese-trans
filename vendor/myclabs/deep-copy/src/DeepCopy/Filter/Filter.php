<?php
/**
 * DeepCopy，过滤器，过滤器
 */

namespace DeepCopy\Filter;

/**
 * Filter to apply to a property while copying an object
 * 筛选器在复制对象时应用于属性
 */
interface Filter
{
    /**
     * Applies the filter to the object.
	 * 将筛选器应用于对象
     *
     * @param object   $object
     * @param string   $property
     * @param callable $objectCopier
     */
    public function apply($object, $property, $objectCopier);
}
