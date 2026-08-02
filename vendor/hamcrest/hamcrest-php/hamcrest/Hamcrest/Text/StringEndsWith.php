<?php
/**
 * Hamcrest，文本，字符串结束
 */

namespace Hamcrest\Text;

/*
 Copyright (c) 2009 hamcrest.org
 */

/**
 * Tests if the argument is a string that ends with a substring.
 * 测试参数是否是以子字符串结尾的字符串。
 */
class StringEndsWith extends SubstringMatcher
{

    public function __construct($substring)
    {
        parent::__construct($substring);
    }

    /**
     * Matches if value is a string that ends with $substring.
	 * 匹配以$substring结尾的字符串
     *
     * @factory
     */
    public static function endsWith($substring)
    {
        return new self($substring);
    }

    // -- Protected Methods

    protected function evalSubstringOf($string)
    {
        return (substr($string, (-1 * strlen($this->_substring))) === $this->_substring);
    }

    protected function relationship()
    {
        return 'ending with';
    }
}
