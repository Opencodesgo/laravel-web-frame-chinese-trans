<?php
/**
 * Ramsey，Uuid，异常，建造者没有发现异常
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
 * Thrown to indicate that no suitable builder could be found
 * 投掷,表明没有合适的建造者
 */
class BuilderNotFoundException extends PhpRuntimeException implements UuidExceptionInterface
{
}
