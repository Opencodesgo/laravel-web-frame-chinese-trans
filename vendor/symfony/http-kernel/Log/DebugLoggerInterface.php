<?php
/**
 * Symfony，Component，HttpKernel，日志，调试日志接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Log;

use Symfony\Component\HttpFoundation\Request;

/**
 * DebugLoggerInterface.
 * 调试日志接口
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface DebugLoggerInterface
{
    /**
     * Returns an array of logs.
	 * 返回一个日志数组。
     *
     * @return array<array{
     *     channel: ?string,
     *     context: array<string, mixed>,
     *     message: string,
     *     priority: int,
     *     priorityName: string,
     *     timestamp: int,
     *     timestamp_rfc3339: string,
     * }>
     */
    public function getLogs(?Request $request = null);

    /**
     * Returns the number of errors.
	 * 返回错误的数目
     *
     * @return int
     */
    public function countErrors(?Request $request = null);

    /**
     * Removes all log records.
	 * 删除所有日志记录
     *
     * @return void
     */
    public function clear();
}
