<?php
/**
 * Psr，Log，日志接口
 */

namespace Psr\Log;

/**
 * Describes a logger instance.
 * 描述记录器实例。
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data. The only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
interface LoggerInterface
{
    /**
     * System is unusable.
	 * 系统不可用
     *
     * @param mixed[] $context
     */
    public function emergency(string|\Stringable $message, array $context = []): void;

    /**
     * Action must be taken immediately.
	 * 必须立即采取行动。
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param mixed[] $context
     */
    public function alert(string|\Stringable $message, array $context = []): void;

    /**
     * Critical conditions.
	 * 临界状态
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param mixed[] $context
     */
    public function critical(string|\Stringable $message, array $context = []): void;

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
	 * 无需立即处理，但通常应记录并监控的运行时错误。
     *
     * @param mixed[] $context
     */
    public function error(string|\Stringable $message, array $context = []): void;

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param mixed[] $context
     */
    public function warning(string|\Stringable $message, array $context = []): void;

    /**
     * Normal but significant events.
	 * 正常但重要的事件
     *
     * @param mixed[] $context
     */
    public function notice(string|\Stringable $message, array $context = []): void;

    /**
     * Interesting events.
	 * 有趣的事件
     *
     * Example: User logs in, SQL logs.
     *
     * @param mixed[] $context
     */
    public function info(string|\Stringable $message, array $context = []): void;

    /**
     * Detailed debug information.
	 * 详细的调试信息
     *
     * @param mixed[] $context
     */
    public function debug(string|\Stringable $message, array $context = []): void;

    /**
     * Logs with an arbitrary level.
	 * 具有任意级别的日志
     *
     * @param mixed $level
     * @param mixed[] $context
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void;
}
