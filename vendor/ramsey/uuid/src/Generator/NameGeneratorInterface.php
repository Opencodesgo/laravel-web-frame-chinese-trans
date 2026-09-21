<?php
/**
 * Ramsey，Uuid，生成器，名称生成器接口
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

use Ramsey\Uuid\UuidInterface;

/**
 * A name generator generates strings of binary data created by hashing together a namespace with a name, according to a
 * hashing algorithm
 */
interface NameGeneratorInterface
{
    /**
     * Generate a binary string from a namespace and name hashed together with the specified hashing algorithm
	 * 使用指定的散列算法从名称空间和名称生成二进制字符串
     *
     * @param UuidInterface $ns The namespace
     * @param string $name The name to use for creating a UUID
     * @param string $hashAlgorithm The hashing algorithm to use
     *
     * @return string A binary string
     *
     * @pure
     */
    public function generate(UuidInterface $ns, string $name, string $hashAlgorithm): string;
}
