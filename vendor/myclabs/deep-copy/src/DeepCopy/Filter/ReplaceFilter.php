<?php
/**
 * DeepCopy，过滤器，替换过滤器
 */

namespace DeepCopy\Filter;

use DeepCopy\Reflection\ReflectionHelper;

/**
 * @final
 */
class ReplaceFilter implements Filter
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param callable $callable Will be called to get the new value for each property to replace
     */
    public function __construct(callable $callable)
    {
        $this->callback = $callable;
    }

    /**
     * Replaces the object property by the result of the callback called with the object property.
	 * 用对象属性调用的回调的结果替换对象属性
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        $value = call_user_func($this->callback, $reflectionProperty->getValue($object));

        $reflectionProperty->setValue($object, $value);
    }
}
