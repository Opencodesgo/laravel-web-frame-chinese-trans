<?php
/**
 * Ramsey，Uuid，生成器，Pecl Uuid 时间发生器
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

namespace Ramsey\Uuid\Generator;

use function uuid_create;
use function uuid_parse;

use const UUID_TYPE_TIME;

/**
 * PeclUuidTimeGenerator generates strings of binary data for time-base UUIDs, using ext-uuid
 * PeclUuidTimeGenerator 使用ext-uuid为基于时间的uuid生成二进制数据字符串
 *
 * @link https://pecl.php.net/package/uuid ext-uuid
 */
class PeclUuidTimeGenerator implements TimeGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate($node = null, ?int $clockSeq = null): string
    {
        $uuid = uuid_create(UUID_TYPE_TIME);

        return (string) uuid_parse($uuid);
    }
}
