<?php
/**
 * Ramsey，Uuid，类型，数字接口
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

namespace Ramsey\Uuid\Type;

/**
 * NumberInterface ensures consistency in numeric values returned by ramsey/uuid
 * NumberInterface保证了ramsey/uuid返回的数值的一致性
 *
 * @immutable
 */
interface NumberInterface extends TypeInterface
{
    /**
     * Returns true if this number is less than zero
	 * 如果此数字小于零，则返回true。
     */
    public function isNegative(): bool;
}
