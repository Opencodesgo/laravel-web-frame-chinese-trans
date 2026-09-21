<?php
/**
 * Ramsey，Uuid，提供者，时间提供程序接口
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

use Ramsey\Uuid\Type\Time;

/**
 * A time provider retrieves the current time
 * 时间提供程序检索当前时间
 */
interface TimeProviderInterface
{
    /**
     * Returns a time object
     */
    public function getTime(): Time;
}
