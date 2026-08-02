<?php declare(strict_types=1);

/**
 * Monolog，处理程序，Zend 监控处理程序
 *

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;

/**
 * Handler sending logs to Zend Monitor
 * 处理发送日志到Zend监视器
 *
 * @author  Christian Bergau <cbergau86@gmail.com>
 * @author  Jason Davis <happydude@jasondavis.net>
 *
 * @phpstan-import-type FormattedRecord from AbstractProcessingHandler
 */
class ZendMonitorHandler extends AbstractProcessingHandler
{
    /**
     * Monolog level / ZendMonitor Custom Event priority map
	 * 独白级别/ ZendMonitor自定义事件优先级图
     *
     * @var array<int, int>
     */
    protected $levelMap = [];

    /**
     * @throws MissingExtensionException
     */
    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        if (!function_exists('zend_monitor_custom_event')) {
            throw new MissingExtensionException(
                'You must have Zend Server installed with Zend Monitor enabled in order to use this handler'
            );
        }
        //zend monitor constants are not defined if zend monitor is not enabled.
		// 如果未启用Zend监视器，则不定义Zend监视器常量。
        $this->levelMap = [
            Logger::DEBUG     => \ZEND_MONITOR_EVENT_SEVERITY_INFO,
            Logger::INFO      => \ZEND_MONITOR_EVENT_SEVERITY_INFO,
            Logger::NOTICE    => \ZEND_MONITOR_EVENT_SEVERITY_INFO,
            Logger::WARNING   => \ZEND_MONITOR_EVENT_SEVERITY_WARNING,
            Logger::ERROR     => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
            Logger::CRITICAL  => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
            Logger::ALERT     => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
            Logger::EMERGENCY => \ZEND_MONITOR_EVENT_SEVERITY_ERROR,
        ];
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record): void
    {
        $this->writeZendMonitorCustomEvent(
            Logger::getLevelName($record['level']),
            $record['message'],
            $record['formatted'],
            $this->levelMap[$record['level']]
        );
    }

    /**
     * Write to Zend Monitor Events
	 * 写入Zend监视器事件
     * @param string $type      Text displayed in "Class Name (custom)" field
     * @param string $message   Text displayed in "Error String"
     * @param array  $formatted Displayed in Custom Variables tab
     * @param int    $severity  Set the event severity level (-1,0,1)
     *
     * @phpstan-param FormattedRecord $formatted
     */
    protected function writeZendMonitorCustomEvent(string $type, string $message, array $formatted, int $severity): void
    {
        zend_monitor_custom_event($type, $message, $formatted, $severity);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultFormatter(): FormatterInterface
    {
        return new NormalizerFormatter();
    }

    /**
     * @return array<int, int>
     */
    public function getLevelMap(): array
    {
        return $this->levelMap;
    }
}
