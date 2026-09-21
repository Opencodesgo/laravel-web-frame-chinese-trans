<?php
/**
 * Ramsey，Uuid，异常，时间源异常
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
 * Thrown to indicate that the source of time encountered an error
 * 抛出以指示时间源遇到错误
 */
class TimeSourceException extends PhpRuntimeException implements UuidExceptionInterface
{
}
