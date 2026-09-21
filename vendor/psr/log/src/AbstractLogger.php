<?php
/**
 * Psr，Log，抽象的记录器
 */

namespace Psr\Log;

/**
 * This is a simple Logger implementation that other Loggers can inherit from.
 * 这是一个简单的Logger实现，其他Logger可以继承它。
 *
 * It simply delegates all log-level-specific methods to the `log` method to
 * reduce boilerplate code that a simple Logger that does the same thing with
 * messages regardless of the error level has to implement.
 */
abstract class AbstractLogger implements LoggerInterface
{
    use LoggerTrait;
}
