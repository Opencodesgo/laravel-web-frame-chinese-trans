<?php
/**
 * Webmozart，分析程序，值
 */

declare(strict_types=1);

namespace Dotenv\Parser;

use Dotenv\Util\Str;

final class Value
{
    /**
     * The string representation of the parsed value.
	 * 解析值的字符串表示
     *
     * @var string
     */
    private $chars;

    /**
     * The locations of the variables in the value.
	 * 值的变量的位置
     *
     * @var int[]
     */
    private $vars;

    /**
     * Internal constructor for a value.
	 * 一个值的内部构造函数
     *
     * @param string $chars
     * @param int[]  $vars
     *
     * @return void
     */
    private function __construct(string $chars, array $vars)
    {
        $this->chars = $chars;
        $this->vars = $vars;
    }

    /**
     * Create an empty value instance.
	 * 创建一个空值实例
     *
     * @return \Dotenv\Parser\Value
     */
    public static function blank()
    {
        return new self('', []);
    }

    /**
     * Create a new value instance, appending the characters.
	 * 创建一个新的值实例,附加字符
     *
     * @param string $chars
     * @param bool   $var
     *
     * @return \Dotenv\Parser\Value
     */
    public function append(string $chars, bool $var)
    {
        return new self(
            $this->chars.$chars,
            $var ? \array_merge($this->vars, [Str::len($this->chars)]) : $this->vars
        );
    }

    /**
     * Get the string representation of the parsed value.
	 * 获取解析值的字符串表示
     *
     * @return string
     */
    public function getChars()
    {
        return $this->chars;
    }

    /**
     * Get the locations of the variables in the value.
	 * 获取值的变量的位置
     *
     * @return int[]
     */
    public function getVars()
    {
        $vars = $this->vars;

        \rsort($vars);

        return $vars;
    }
}
