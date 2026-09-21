<?php
/**
 * Symfony，Component，VarDumper，克隆，存储器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Cloner;

/**
 * DumperInterface used by Data objects.
 * dumperdata对象使用的接口。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface DumperInterface
{
    /**
     * Dumps a scalar value.
	 * 转储标量值
     *
     * @return void
     */
    public function dumpScalar(Cursor $cursor, string $type, string|int|float|bool|null $value);

    /**
     * Dumps a string.
	 * 转储字符串
     *
     * @param string $str The string being dumped
     * @param bool   $bin Whether $str is UTF-8 or binary encoded
     * @param int    $cut The number of characters $str has been cut by
     *
     * @return void
     */
    public function dumpString(Cursor $cursor, string $str, bool $bin, int $cut);

    /**
     * Dumps while entering an hash.
	 * 在输入散列时转储
     *
     * @param int             $type     A Cursor::HASH_* const for the type of hash
     * @param string|int|null $class    The object class, resource type or array count
     * @param bool            $hasChild When the dump of the hash has child item
     *
     * @return void
     */
    public function enterHash(Cursor $cursor, int $type, string|int|null $class, bool $hasChild);

    /**
     * Dumps while leaving an hash.
	 * 转储，同时留下散列。
     *
     * @param int             $type     A Cursor::HASH_* const for the type of hash
     * @param string|int|null $class    The object class, resource type or array count
     * @param bool            $hasChild When the dump of the hash has child item
     * @param int             $cut      The number of items the hash has been cut by
     *
     * @return void
     */
    public function leaveHash(Cursor $cursor, int $type, string|int|null $class, bool $hasChild, int $cut);
}
