<?php
/**
 * Hamcrest，数组，是否数组包含
 */

namespace Hamcrest\Arrays;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\Matcher;
use Hamcrest\TypeSafeMatcher;
use Hamcrest\Util;

/**
 * Matches if an array contains an item satisfying a nested matcher.
 * 匹配如果一个数组包含一个项目,满足一个嵌套的matcher。
 */
class IsArrayContaining extends TypeSafeMatcher
{

    private $_elementMatcher;

    public function __construct(Matcher $elementMatcher)
    {
        parent::__construct(self::TYPE_ARRAY);

        $this->_elementMatcher = $elementMatcher;
    }

    protected function matchesSafely($array)
    {
        foreach ($array as $element) {
            if ($this->_elementMatcher->matches($element)) {
                return true;
            }
        }

        return false;
    }

    protected function describeMismatchSafely($array, Description $mismatchDescription)
    {
        $mismatchDescription->appendText('was ')->appendValue($array);
    }

    public function describeTo(Description $description)
    {
        $description
                 ->appendText('an array containing ')
                 ->appendDescriptionOf($this->_elementMatcher)
        ;
    }

    /**
     * Evaluates to true if any item in an array satisfies the given matcher.
	 * 如果数组中的任何项满足给定的matcher,则评估为true。
     *
     * @param mixed $item as a {@link Hamcrest\Matcher} or a value.
     *
     * @return \Hamcrest\Arrays\IsArrayContaining
     * @factory hasValue
     */
    public static function hasItemInArray($item)
    {
        return new self(Util::wrapValueWithIsEqual($item));
    }
}
