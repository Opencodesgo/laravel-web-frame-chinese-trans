<?php
/**
 * Ramsey，Uuid，验证器，验证器接口
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

namespace Ramsey\Uuid\Validator;

/**
 * A validator validates a string as a proper UUID
 * 验证器将字符串作为正确的UUID进行验证
 *
 * @immutable
 */
interface ValidatorInterface
{
    /**
     * Returns the regular expression pattern used by this validator
	 * 返回此验证器使用的正则表达式模式
     *
     * @return non-empty-string The regular expression pattern this validator uses
     */
    public function getPattern(): string;

    /**
     * Returns true if the provided string represents a UUID
	 * 如果提供的字符串表示UUID，则返回true。
     *
     * @param string $uuid The string to validate as a UUID
     *
     * @return bool True if the string is a valid UUID, false otherwise
     *
     * @pure
     */
    public function validate(string $uuid): bool;
}
