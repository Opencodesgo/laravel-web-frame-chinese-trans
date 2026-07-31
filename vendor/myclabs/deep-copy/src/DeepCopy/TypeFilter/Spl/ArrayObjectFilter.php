<?php
/**
 * DeepCopy，类型过滤器，Spl，数组对象过滤器
 */

namespace DeepCopy\TypeFilter\Spl;

use DeepCopy\DeepCopy;
use DeepCopy\TypeFilter\TypeFilter;

/**
 * In PHP 7.4 the storage of an ArrayObject isn't returned as
 * ReflectionProperty. So we deep copy its array copy.
 * 在PHP 7.4中，ArrayObject的存储不返回为 ReflectionProperty。我们深度复制它的数组拷贝。
 */
final class ArrayObjectFilter implements TypeFilter
{
    /**
     * @var DeepCopy
     */
    private $copier;

    public function __construct(DeepCopy $copier)
    {
        $this->copier = $copier;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($arrayObject)
    {
        $clone = clone $arrayObject;
        foreach ($arrayObject->getArrayCopy() as $k => $v) {
            $clone->offsetSet($k, $this->copier->copy($v));
        }

        return $clone;
    }
}

