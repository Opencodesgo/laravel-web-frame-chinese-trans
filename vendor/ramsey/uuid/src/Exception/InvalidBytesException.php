<?php
/**
 * Ramsey，Uuid，异常，无效字节异常
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
 * 抛出,以表明在某些方面操作的字节无效
 */
class InvalidBytesException extends PhpRuntimeException implements UuidExceptionInterface
{
}
