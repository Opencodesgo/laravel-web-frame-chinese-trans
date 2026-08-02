<?php
/**
 * Hamcrest，Text，字符串开始
 */

namespace Hamcrest\Text;

/*
 Copyright (c) 2009 hamcrest.org
 */

/**
 * Tests if the argument is a string that contains a substring.
 * 测试参数是否为包含子字符串的字符串。
 */
class StringStartsWith extends SubstringMatcher
{

    public function __construct($substring)
    {
        parent::__construct($substring);
    }

    /**
     * Matches if value is a string that starts with $substring.
	 * 匹配如果值是一个以$ substring开头的字符串
     *
     * @factory
     */
    public static function startsWith($substring)
    {
        return new self($substring);
    }

    // -- Protected Methods

    protected function evalSubstringOf($string)
    {
        return (substr($string, 0, strlen($this->_substring)) === $this->_substring);
    }

    protected function relationship()
    {
        return 'starting with';
    }
}
