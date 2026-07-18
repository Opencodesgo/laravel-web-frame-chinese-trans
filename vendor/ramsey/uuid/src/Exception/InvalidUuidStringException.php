<?php
/**
 * Ramsey，Uuid，异常，无效的Uuid字符串异常
 */

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://benramsey.com/projects/ramsey-uuid/ Documentation
 * @link https://packagist.org/packages/ramsey/uuid Packagist
 * @link https://github.com/ramsey/uuid GitHub
 */

namespace Ramsey\Uuid\Exception;

use InvalidArgumentException;

/**
 * Thrown to indicate that the parsed UUID string is invalid.
 * 被抛出,以指示解析的UUID字符串无效。
 */
class InvalidUuidStringException extends InvalidArgumentException
{
}
