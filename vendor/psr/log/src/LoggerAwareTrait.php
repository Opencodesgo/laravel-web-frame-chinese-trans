<?php
/**
 * Psr，Log，记录器感知特性
 */

namespace Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 * logerawareinterface 的基本实现。
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
	 * 日志程序实例
     */
    protected ?LoggerInterface $logger = null;

    /**
     * Sets a logger.
	 * 设置记录器
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
