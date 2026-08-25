<?php declare(strict_types=1);

/**
 * Monolog，处理器，内省处理机
 */

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Processor;

use Monolog\Logger;
use Psr\Log\LogLevel;

/**
 * Injects line/file:class/function where the log message came from
 * 注入行/文件:来自日志消息的类/函数
 *
 * Warning: This only works if the handler processes the logs directly.
 * If you put the processor on a handler that is behind a FingersCrossedHandler
 * for example, the processor will only be called once the trigger level is reached,
 * and all the log records will have the same file/line/.. data from the call that
 * triggered the FingersCrossedHandler.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Level from \Monolog\Logger
 * @phpstan-import-type LevelName from \Monolog\Logger
 */
class IntrospectionProcessor implements ProcessorInterface
{
    /** @var int */
    private $level;
    /** @var string[] */
    private $skipClassesPartials;
    /** @var int */
    private $skipStackFramesCount;
    /** @var string[] */
    private $skipFunctions = [
        'call_user_func',
        'call_user_func_array',
    ];

    /**
     * @param string|int $level               The minimum logging level at which this Processor will be triggered
     * @param string[]   $skipClassesPartials
     *
     * @phpstan-param Level|LevelName|LogLevel::* $level
     */
    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = [], int $skipStackFramesCount = 0)
    {
        $this->level = Logger::toMonologLevel($level);
        $this->skipClassesPartials = array_merge(['Monolog\\'], $skipClassesPartials);
        $this->skipStackFramesCount = $skipStackFramesCount;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        // return if the level is not high enough
		// 如果级别不够高，返回。
        if ($record['level'] < $this->level) {
            return $record;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // skip first since it's always the current method
		// 先跳过，因为它总是当前方法。
        array_shift($trace);
        // the call_user_func call is also skipped
		// call_user_func调用也被跳过
        array_shift($trace);

        $i = 0;

        while ($this->isTraceClassOrSkippedFunction($trace, $i)) {
            if (isset($trace[$i]['class'])) {
                foreach ($this->skipClassesPartials as $part) {
                    if (strpos($trace[$i]['class'], $part) !== false) {
                        $i++;

                        continue 2;
                    }
                }
            } elseif (in_array($trace[$i]['function'], $this->skipFunctions)) {
                $i++;

                continue;
            }

            break;
        }

        $i += $this->skipStackFramesCount;

        // we should have the call source now
		// 我们现在应该有呼叫源了
        $record['extra'] = array_merge(
            $record['extra'],
            [
                'file'      => isset($trace[$i - 1]['file']) ? $trace[$i - 1]['file'] : null,
                'line'      => isset($trace[$i - 1]['line']) ? $trace[$i - 1]['line'] : null,
                'class'     => isset($trace[$i]['class']) ? $trace[$i]['class'] : null,
                'callType'  => isset($trace[$i]['type']) ? $trace[$i]['type'] : null,
                'function'  => isset($trace[$i]['function']) ? $trace[$i]['function'] : null,
            ]
        );

        return $record;
    }

    /**
     * @param array[] $trace
     */
    private function isTraceClassOrSkippedFunction(array $trace, int $index): bool
    {
        if (!isset($trace[$index])) {
            return false;
        }

        return isset($trace[$index]['class']) || in_array($trace[$index]['function'], $this->skipFunctions);
    }
}
