<?php
/**
 * Ramsey，Uuid，异常，不支持的操作异常
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

use LogicException as PhpLogicException;

/**
 * Thrown to indicate that the requested operation is not supported
 * 抛出以指示不支持所请求的操作
 */
class UnsupportedOperationException extends PhpLogicException implements UuidExceptionInterface
{
}
