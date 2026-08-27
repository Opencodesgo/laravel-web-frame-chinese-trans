<?php
/**
 * Doctrine，Common，Lexer，令牌
 */

declare(strict_types=1);

namespace Doctrine\Common\Lexer;

use UnitEnum;

use function in_array;

/**
 * @template T of UnitEnum|string|int
 * @template V of string|int
 */
final class Token
{
    /**
     * The string value of the token in the input string
	 * 输入字符串中记号的字符串值
     *
     * @readonly
     * @var V
     */
    public string|int $value;

    /**
     * The type of the token (identifier, numeric, string, input parameter, none)
	 * 标记的类型(标识符、数字、字符串、输入参数、无)
     *
     * @readonly
     * @var T|null
     */
    public $type;

    /**
     * The position of the token in the input string
	 * 记号在输入字符串中的位置
     *
     * @readonly
     */
    public int $position;

    /**
     * @param V      $value
     * @param T|null $type
     */
    public function __construct(string|int $value, $type, int $position)
    {
        $this->value    = $value;
        $this->type     = $type;
        $this->position = $position;
    }

    /** @param T ...$types */
    public function isA(...$types): bool
    {
        return in_array($this->type, $types, true);
    }
}
