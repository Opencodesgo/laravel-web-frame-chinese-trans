<?php
/**
 * Ramsey，Uuid，生成器，随机发生器接口
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

/**
 * A random generator generates strings of random binary data
 * 随机生成器生成随机二进制数据字符串
 */
interface RandomGeneratorInterface
{
    /**
     * Generates a string of randomized binary data
	 * 生成一串随机二进制数据
     *
     * @param int<1, max> $length The number of bytes to generate of random binary data
     *
     * @return string A binary string
     */
    public function generate(int $length): string;
}
