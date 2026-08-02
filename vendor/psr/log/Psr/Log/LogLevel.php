<?php
/**
 * Psr，Log，日志级别
 */

namespace Psr\Log;

/**
 * Describes log levels.
 * 描述日志级别
 */
class LogLevel
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
}
