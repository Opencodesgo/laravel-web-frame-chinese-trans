<?php
/**
 * Hamcrest，文本，字符串包含
 */

namespace Hamcrest\Text;

/*
 Copyright (c) 2009 hamcrest.org
 */

/**
 * Tests if the argument is a string that contains a substring.
 * 测试参数是否为包含子字符串的字符串。
 */
class StringContains extends SubstringMatcher
{

    public function __construct($substring)
    {
        parent::__construct($substring);
    }

    public function ignoringCase()
    {
        return new StringContainsIgnoringCase($this->_substring);
    }

    /**
     * Matches if value is a string that contains $substring.
	 * 匹配如果value是一个包含$substring的字符串
     *
     * @factory
     */
    public static function containsString($substring)
    {
        return new self($substring);
    }

    // -- Protected Methods

    protected function evalSubstringOf($item)
    {
        return (false !== strpos((string) $item, $this->_substring));
    }

    protected function relationship()
    {
        return 'containing';
    }
}
