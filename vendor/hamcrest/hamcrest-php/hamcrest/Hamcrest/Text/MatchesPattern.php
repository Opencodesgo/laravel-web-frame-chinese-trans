<?php
/**
 * Hamcrest，Text，匹配模式
 */

namespace Hamcrest\Text;

/*
 Copyright (c) 2010 hamcrest.org
 */

/**
 * Tests if the argument is a string that matches a regular expression.
 * 测试参数是否为与正则表达式匹配的字符串。
 */
class MatchesPattern extends SubstringMatcher
{

    public function __construct($pattern)
    {
        parent::__construct($pattern);
    }

    /**
     * Matches if value is a string that matches regular expression $pattern.
	 * 如果value是匹配正则表达式$pattern的字符串，则进行匹配。
     *
     * @factory
     */
    public static function matchesPattern($pattern)
    {
        return new self($pattern);
    }

    // -- Protected Methods

    protected function evalSubstringOf($item)
    {
        return preg_match($this->_substring, (string) $item) >= 1;
    }

    protected function relationship()
    {
        return 'matching';
    }
}
