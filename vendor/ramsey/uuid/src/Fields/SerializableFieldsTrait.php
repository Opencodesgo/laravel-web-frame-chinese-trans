<?php
/**
 * Ramsey，Uuid，字段，可序列化字段特性
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

namespace Ramsey\Uuid\Fields;

use ValueError;

use function base64_decode;
use function sprintf;
use function strlen;

/**
 * Provides common serialization functionality to fields
 * 为字段提供通用的序列化功能
 *
 * @immutable
 */
trait SerializableFieldsTrait
{
    /**
     * @param string $bytes The bytes that comprise the fields
     */
    abstract public function __construct(string $bytes);

    /**
     * Returns the bytes that comprise the fields
	 * 返回组成字段的字节
     */
    abstract public function getBytes(): string;

    /**
     * Returns a string representation of the object
	 * 返回对象的字符串表示形式
     */
    public function serialize(): string
    {
        return $this->getBytes();
    }

    /**
     * @return array{bytes: string}
     */
    public function __serialize(): array
    {
        return ['bytes' => $this->getBytes()];
    }

    /**
     * Constructs the object from a serialized string representation
	 * 从序列化字符串表示构造对象
     *
     * @param string $data The serialized string representation of the object
     */
    public function unserialize(string $data): void
    {
        if (strlen($data) === 16) {
            $this->__construct($data);
        } else {
            $this->__construct(base64_decode($data));
        }
    }

    /**
     * @param array{bytes?: string} $data
     */
    public function __unserialize(array $data): void
    {
        // @codeCoverageIgnoreStart
        if (!isset($data['bytes'])) {
            throw new ValueError(sprintf('%s(): Argument #1 ($data) is invalid', __METHOD__));
        }
        // @codeCoverageIgnoreEnd

        $this->unserialize($data['bytes']);
    }
}
