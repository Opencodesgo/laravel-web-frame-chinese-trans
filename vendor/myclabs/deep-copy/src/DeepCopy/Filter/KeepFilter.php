<?php
/**
 * DeepCopy，过滤器，保持过滤器
 */

namespace DeepCopy\Filter;

class KeepFilter implements Filter
{
    /**
     * Keeps the value of the object property.
	 * 保留对象属性的值
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        // Nothing to do
    }
}
