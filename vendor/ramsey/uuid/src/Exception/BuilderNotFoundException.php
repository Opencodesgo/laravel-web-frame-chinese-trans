<?php
/**
 * Ramsey，Uuid，异常，构建器未发现异常
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
 * 抛出以指示找不到合适的构建器
 */
class BuilderNotFoundException extends PhpRuntimeException implements UuidExceptionInterface
{
}
