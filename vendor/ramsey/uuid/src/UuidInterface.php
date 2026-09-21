<?php
/**
 * Ramsey，Uuid，Uuid 接口
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

namespace Ramsey\Uuid;

use JsonSerializable;
use Ramsey\Uuid\Fields\FieldsInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Serializable;
use Stringable;

/**
 * A UUID is a universally unique identifier adhering to an agreed-upon representation format and standard for generation
 * UUID是一种普遍唯一的标识符，它遵循一种商定的表示格式和生成标准。
 *
 * @immutable
 */
interface UuidInterface extends
    DeprecatedUuidInterface,
    JsonSerializable,
    Serializable,
    Stringable
{
    /**
     * Returns -1, 0, or 1 if the UUID is less than, equal to, or greater than the other UUID
     *
     * The first of two UUIDs is greater than the second if the most significant field in which the UUIDs differ is
     * greater for the first UUID.
     *
     * @param UuidInterface $other The UUID to compare
     *
     * @return int<-1,1> -1, 0, or 1 if the UUID is less than, equal to, or greater than $other
     */
    public function compareTo(UuidInterface $other): int;

    /**
     * Returns true if the UUID is equal to the provided object
	 * 如果UUID等于所提供的对象，则返回true。
     *
     * The result is true if and only if the argument is not null, is a UUID object, has the same variant, and contains
     * the same value, bit-for-bit, as the UUID.
     *
     * @param object | null $other An object to test for equality with this UUID
     *
     * @return bool True if the other object is equal to this UUID
     */
    public function equals(?object $other): bool;

    /**
     * Returns the binary string representation of the UUID
	 * 返回UUID的二进制字符串表示形式
     *
     * @return non-empty-string
     *
     * @pure
     */
    public function getBytes(): string;

    /**
     * Returns the fields that comprise this UUID
	 * 返回组成此UUID的字段
     */
    public function getFields(): FieldsInterface;

    /**
     * Returns the hexadecimal representation of the UUID
	 * 返回UUID的十六进制表示形式
     */
    public function getHex(): Hexadecimal;

    /**
     * Returns the integer representation of the UUID
	 * 返回UUID的整数表示形式
     */
    public function getInteger(): IntegerObject;

    /**
     * Returns the string standard representation of the UUID as a URN
	 * 返回UUID的字符串标准表示形式为URN
     *
     * @link http://en.wikipedia.org/wiki/Uniform_Resource_Name Uniform Resource Name
     * @link https://www.rfc-editor.org/rfc/rfc9562.html#section-4 RFC 9562, 4. UUID Format
     * @link https://www.rfc-editor.org/rfc/rfc9562.html#section-7 RFC 9562, 7. IANA Considerations
     * @link https://www.rfc-editor.org/rfc/rfc4122.html#section-3 RFC 4122, 3. Namespace Registration Template
     */
    public function getUrn(): string;

    /**
     * Returns the string standard representation of the UUID
	 * 返回UUID的字符串标准表示形式
     *
     * @return non-empty-string
     *
     * @pure
     */
    public function toString(): string;

    /**
     * Casts the UUID to the string standard representation
	 * 将UUID强制转换为字符串标准表示形式
     *
     * @return non-empty-string
     *
     * @pure
     */
    public function __toString(): string;
}
