<?php
/**
 * Ramsey，Uuid，异常，无效的字节异常
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
 * Thrown to indicate that the bytes being operated on are invalid in some way
 * 抛出以指示正在操作的字节在某种程度上是无效的
 */
class InvalidBytesException extends PhpRuntimeException implements UuidExceptionInterface
{
}
