<?php
/**
 * DeepCopy，类型过滤器，Spl，Shallow Copy 过滤器
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
