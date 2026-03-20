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
	 * 调试日志接口
     *
     * A log is an array with the following mandatory keys:
     * timestamp, message, priority, and priorityName.
     * It can also have an optional context key containing an array.
     *
     * @return array
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
     */
    public function clear();
}
