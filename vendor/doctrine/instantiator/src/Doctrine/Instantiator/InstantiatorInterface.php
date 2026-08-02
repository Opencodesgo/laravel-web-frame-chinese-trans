<?php
/**
 * Doctrine，Instantiator，实例化器接口
 */

namespace Doctrine\Instantiator;

use Doctrine\Instantiator\Exception\ExceptionInterface;

/**
 * Instantiator provides utility methods to build objects without invoking their constructors
 * Instantiator提供实用程序方法来构建对象，而无需调用其构造函数。
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
