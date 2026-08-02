<?php
/**
 * DeepCopy，类型过滤器，浅拷贝过滤器
 */

namespace DeepCopy\TypeFilter;

/**
 * @final
 */
class ShallowCopyFilter implements TypeFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply($element)
    {
        return clone $element;
    }
}
