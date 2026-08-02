<?php
/**
 * Hamcrest，文本，是否等于忽略大小写
 */

namespace Hamcrest\Text;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\TypeSafeMatcher;

/**
 * Tests if a string is equal to another string, regardless of the case.
 * 测试一个字符串是否等于另一个字符串，而不考虑情况。
 */
class IsEqualIgnoringCase extends TypeSafeMatcher
{

    private $_string;

    public function __construct($string)
    {
        parent::__construct(self::TYPE_STRING);

        $this->_string = $string;
    }

    protected function matchesSafely($item)
    {
        return strtolower($this->_string) === strtolower($item);
    }

    protected function describeMismatchSafely($item, Description $mismatchDescription)
    {
        $mismatchDescription->appendText('was ')->appendText($item);
    }

    public function describeTo(Description $description)
    {
        $description->appendText('equalToIgnoringCase(')
                                ->appendValue($this->_string)
                                ->appendText(')')
                                ;
    }

    /**
     * Matches if value is a string equal to $string, regardless of the case.
	 * 匹配如果value是一个等于$string的字符串，无论哪种情况。
     *
     * @factory
     */
    public static function equalToIgnoringCase($string)
    {
        return new self($string);
    }
}
