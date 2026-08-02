<?php
/**
 * Dotenv，加载器，Value
 */

namespace Dotenv\Loader;

class Value
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
    private function __construct($chars, array $vars)
    {
        $this->chars = $chars;
        $this->vars = $vars;
    }

    /**
     * Create an empty value instance.
	 * 创建一个空值实例
     *
     * @return \Dotenv\Loader\Value
     */
    public static function blank()
    {
        return new self('', []);
    }

    /**
     * Create a new value instance, appending the character.
     *
     * @param string $char
     * @param bool   $var
     *
     * @return \Dotenv\Loader\Value
     */
    public function append($char, $var)
    {
        return new self(
            $this->chars.$char,
            $var ? array_merge($this->vars, [strlen($this->chars)]) : $this->vars
        );
    }

    /**
     * Get the string representation of the parsed value.
     *
     * @return string
     */
    public function getChars()
    {
        return $this->chars;
    }

    /**
     * Get the locations of the variables in the value.
     *
     * @return int[]
     */
    public function getVars()
    {
        $vars = $this->vars;
        rsort($vars);

        return $vars;
    }
}
