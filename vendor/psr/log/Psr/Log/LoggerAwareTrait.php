<?php
/**
 * Psr，Log，记录器感知特性
 */

namespace Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 * LoggerAwareInterface的基本实现。
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
	 * 日志程序实例
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Sets a logger.
	 * 设置记录器
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
