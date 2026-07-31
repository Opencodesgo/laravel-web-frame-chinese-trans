<?php
/**
 * Psr，Container，容器异常接口
 */

namespace Psr\Container;

use Throwable;

/**
 * Base interface representing a generic exception in a container.
 * 在容器中表示泛型异常的基接口。
 */
interface ContainerExceptionInterface extends Throwable
{
}
