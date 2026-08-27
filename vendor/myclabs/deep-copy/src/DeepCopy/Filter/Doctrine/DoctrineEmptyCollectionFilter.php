<?php
/**
 * DeepCopy，过滤器，主义，Doctrine 空集合过滤器
 */

namespace DeepCopy\Filter\Doctrine;

use DeepCopy\Filter\Filter;
use DeepCopy\Reflection\ReflectionHelper;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @final
 */
class DoctrineEmptyCollectionFilter implements Filter
{
    /**
     * Sets the object property to an empty doctrine collection.
	 * 将对象属性设置为空原则集合
     *
     * @param object   $object
     * @param string   $property
     * @param callable $objectCopier
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        if (PHP_VERSION_ID < 80100) {
            $reflectionProperty->setAccessible(true);
        }

        $reflectionProperty->setValue($object, new ArrayCollection());
    }
} 