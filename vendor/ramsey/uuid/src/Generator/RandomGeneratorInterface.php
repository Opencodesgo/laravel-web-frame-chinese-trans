<?php
/**
 * Ramsey，Uuid，生成器，随机生成器接口
 */

/**
 * This file is part of the ramsey/uuid library
 * 这个文件是ramsey/uuid库的一部分
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Uuid\Generator;

/**
 * A random generator generates strings of random binary data
 */
interface RandomGeneratorInterface
{
    /**
     * Generates a string of randomized binary data
     *
     * @param int $length The number of bytes of random binary data to generate
     *
     * @return string A binary string
     */
    public function generate(int $length): string;
}
