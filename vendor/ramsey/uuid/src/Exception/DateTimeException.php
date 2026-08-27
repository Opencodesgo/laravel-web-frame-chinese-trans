<?php
/**
 * Ramsey，Uuid，异常，日期时间异常
 */

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Uuid\Exception;

use RuntimeException as PhpRuntimeException;

/**
 * Thrown to indicate that the PHP DateTime extension encountered an exception/error
 * 抛出以指示PHP DateTime扩展遇到异常/错误
 */
class DateTimeException extends PhpRuntimeException implements UuidExceptionInterface
{
}
