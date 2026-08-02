<?php
/**
 * Doctrine，实例化器，Instantiator 接口
 */

namespace Doctrine\Instantiator;

use Doctrine\Instantiator\Exception\ExceptionInterface;

/**
 * Instantiator provides utility methods to build objects without invoking their constructors
 * 实例化器提供了在不调用构造函数的情况下构建对象的实用方法。
 */
interface InstantiatorInterface
{
    /**
     * @param string $className
     * @phpstan-param class-string<T> $className
     *
     * @return object
     * @phpstan-return T
     *
     * @throws ExceptionInterface
     *
     * @template T of object
     */
    public function instantiate($className);
}
