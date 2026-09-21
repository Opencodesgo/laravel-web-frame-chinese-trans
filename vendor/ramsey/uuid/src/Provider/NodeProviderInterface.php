<?php
/**
 * Ramsey，Uuid，提供者，节点提供程序接口
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

use Ramsey\Uuid\Type\Hexadecimal;

/**
 * A node provider retrieves or generates a node ID
 * 节点提供程序检索或生成节点ID
 */
interface NodeProviderInterface
{
    /**
     * Returns a node ID
	 * 返回节点ID
     *
     * @return Hexadecimal The node ID as a hexadecimal string
     */
    public function getNode(): Hexadecimal;
}
