<?php
/**
 * Psy，日志，零记录器
 */

namespace Psr\Log;

/**
 * This Logger can be used to avoid conditional log calls.
 * 这个Logger可以用于避免有条件的日志调用。
 *
 * Logging should always be optional, and if no logger is provided to your
 * library creating a NullLogger instance to have something to throw logs at
 * is a good way to avoid littering your code with `if ($this->logger) { }`
 * blocks.
 */
class NullLogger extends AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        // noop
    }
}
