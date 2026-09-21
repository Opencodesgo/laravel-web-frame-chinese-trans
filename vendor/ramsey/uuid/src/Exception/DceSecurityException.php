<?php
/**
 * Ramsey，Uuid，异常，Dce 安全异常
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
 * Thrown to indicate an exception occurred while dealing with DCE Security (version 2) UUIDs
 * 抛出以指示在处理DCE Security（版本2）uid时发生异常
 */
class DceSecurityException extends PhpRuntimeException implements UuidExceptionInterface
{
}
