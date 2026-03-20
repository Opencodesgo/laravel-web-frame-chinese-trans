<?php
/**
 * Psr，Container，容器异常接口
 */

namespace Psr\Container;

use Throwable;

/**
 * Base interface representing a generic exception in a container.
 * 表示容器中的泛型异常的基接口
 */
interface ContainerExceptionInterface extends Throwable
{
}
