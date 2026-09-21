<?php
/**
 * DeepCopy，过滤器，设置空过滤器
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
	 * 将对象属性设置为空
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);

        if (PHP_VERSION_ID < 80100) {
            $reflectionProperty->setAccessible(true);
        }
        $reflectionProperty->setValue($object, null);
    }
}
