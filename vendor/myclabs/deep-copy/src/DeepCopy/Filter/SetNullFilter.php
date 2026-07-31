<?php
/**
 * DeepCopy，过滤器，设置零过滤器
 */

namespace DeepCopy\Filter;

use DeepCopy\Reflection\ReflectionHelper;

/**
 * @final
 */
class SetNullFilter implements Filter
{
    /**
     * Sets the object property to null.
     * 将对象属性设置为null
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, null);
    }
}
