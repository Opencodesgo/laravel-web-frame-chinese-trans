<?php
/**
 * Psr，Container，未发现异常接口
 */

namespace Psr\Container;

/**
 * No entry was found in the container.
 * 集装箱里没有发现入口
 */
interface NotFoundExceptionInterface extends ContainerExceptionInterface
{
}
