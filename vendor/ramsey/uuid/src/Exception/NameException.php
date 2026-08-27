<?php
/**
 * Ramsey，Uuid，异常，运行时异常
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
 * Thrown to indicate that an error occurred while attempting to hash a namespace and name
 * 抛出以指示在尝试散列命名空间和名称时发生错误
 */
class NameException extends PhpRuntimeException implements UuidExceptionInterface
{
}
