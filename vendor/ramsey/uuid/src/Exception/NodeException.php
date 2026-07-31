<?php
/**
 * Ramsey，Uuid，异常，节点异常
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
 * Thrown to indicate that attempting to fetch or create a node ID encountered an error
 * 抛出试图提取或创建节点ID时遇到错误
 */
class NodeException extends PhpRuntimeException implements UuidExceptionInterface
{
}
