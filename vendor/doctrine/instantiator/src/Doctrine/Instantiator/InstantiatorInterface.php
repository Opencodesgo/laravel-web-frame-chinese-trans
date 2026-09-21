<?php
/**
 * Doctrine，Instantiator，实例化器接口
 */

declare(strict_types=1);

namespace Doctrine\Instantiator;

use Doctrine\Instantiator\Exception\ExceptionInterface;

/**
 * Instantiator provides utility methods to build objects without invoking their constructors
 * Instantiator提供实用程序方法来构建对象，而无需调用其构造函数。
 */
interface InstantiatorInterface
{
    /**
     * @phpstan-param class-string<T> $className
     *
     * @phpstan-return T
     *
     * @throws ExceptionInterface
     *
     * @template T of object
     */
    public function instantiate(string $className): object;
}
