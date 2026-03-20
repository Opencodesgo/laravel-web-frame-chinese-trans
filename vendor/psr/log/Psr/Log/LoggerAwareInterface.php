<?php
/**
 * Psr，Log，记录器感知接口
 */

namespace Psr\Log;

/**
 * Describes a logger-aware instance.
 * 描述可感知记录器的实例
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
	 * 在对象上设置记录器实例
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger);
}
