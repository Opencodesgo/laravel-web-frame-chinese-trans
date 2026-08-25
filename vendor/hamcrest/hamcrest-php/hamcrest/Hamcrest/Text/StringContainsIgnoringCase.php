<?php
/**
 * Hamcrest，文本，字符串包含忽略大小写
 */

namespace Hamcrest\Text;

/*
 Copyright (c) 2010 hamcrest.org
 */

/**
 * Tests if the argument is a string that contains a substring ignoring case.
 * 测试参数是否为包含忽略大小写的子字符串的字符串。
 */
class StringContainsIgnoringCase extends SubstringMatcher
{

    public function __construct($substring)
    {
        parent::__construct($substring);
    }

    /**
     * Matches if value is a string that contains $substring regardless of the case.
	 * 如果value是包含$substring的字符串，则匹配。
     *
     * @factory
     */
    public static function containsStringIgnoringCase($substring)
    {
        return new self($substring);
    }

    // -- Protected Methods

    protected function evalSubstringOf($item)
    {
        return (false !== stripos((string) $item, $this->_substring));
    }

    protected function relationship()
    {
        return 'containing in any case';
    }
}
