<?php
/**
 * Psr，SimpleCache，无效参数异常
 */

namespace Psr\SimpleCache;

/**
 * Exception interface for invalid cache arguments.
 * 无效缓存参数的异常接口
 *
 * When an invalid argument is passed it must throw an exception which implements
 * this interface
 */
interface InvalidArgumentException extends CacheException
{
}
