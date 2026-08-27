<?php
/**
 * Ramsey，Uuid，提供者，Dce 安全提供程序接口
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

namespace Ramsey\Uuid\Provider;

use Ramsey\Uuid\Rfc4122\UuidV2;
use Ramsey\Uuid\Type\Integer as IntegerObject;

/**
 * A DCE provider provides access to local domain identifiers for version 2, DCE Security, UUIDs
 * DCE 提供程序提供对版本2、DCE Security、uuid的本地域标识符的访问
 *
 * @see UuidV2
 */
interface DceSecurityProviderInterface
{
    /**
     * Returns a user identifier for the system
	 * 返回系统的用户标识符
     *
     * @link https://en.wikipedia.org/wiki/User_identifier User identifier
     */
    public function getUid(): IntegerObject;

    /**
     * Returns a group identifier for the system
	 * 返回系统的组标识符
     *
     * @link https://en.wikipedia.org/wiki/Group_identifier Group identifier
     */
    public function getGid(): IntegerObject;
}
