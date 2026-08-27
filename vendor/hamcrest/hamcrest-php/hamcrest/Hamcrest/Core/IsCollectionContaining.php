<?php
/**
 * Hamcrest，核心，是否集合包含
 */

namespace Hamcrest\Core;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\Matcher;
use Hamcrest\TypeSafeMatcher;
use Hamcrest\Util;

/**
 * Tests if an array contains values that match one or more Matchers.
 * 测试如果一个数组包含匹配一个或多个Matchers的值。
 */
class IsCollectionContaining extends TypeSafeMatcher
{

    private $_elementMatcher;

    public function __construct(Matcher $elementMatcher)
    {
        parent::__construct(self::TYPE_ARRAY);

        $this->_elementMatcher = $elementMatcher;
    }

    protected function matchesSafely($items)
    {
        foreach ($items as $item) {
            if ($this->_elementMatcher->matches($item)) {
                return true;
            }
        }

        return false;
    }

    protected function describeMismatchSafely($items, Description $mismatchDescription)
    {
        $mismatchDescription->appendText('was ')->appendValue($items);
    }

    public function describeTo(Description $description)
    {
        $description
                ->appendText('a collection containing ')
                ->appendDescriptionOf($this->_elementMatcher)
                ;
    }

    /**
     * Test if the value is an array containing this matcher.
	 * 测试如果值是包含该matcher的数组。
     *
     * Example:
     * <pre>
     * assertThat(array('a', 'b'), hasItem(equalTo('b')));
     * //Convenience defaults to equalTo()
     * assertThat(array('a', 'b'), hasItem('b'));
     * </pre>
     *
     * @factory ...
     */
    public static function hasItem()
    {
        $args = func_get_args();
        $firstArg = array_shift($args);

        return new self(Util::wrapValueWithIsEqual($firstArg));
    }

    /**
     * Test if the value is an array containing elements that match all of these
     * matchers.
	 * 测试该值是否为包含与所有匹配器都相匹配元素的数组。
     *
     * Example:
     * <pre>
     * assertThat(array('a', 'b', 'c'), hasItems(equalTo('a'), equalTo('b')));
     * </pre>
     *
     * @factory ...
     */
    public static function hasItems(/* args... */)
    {
        $args = func_get_args();
        $matchers = array();

        foreach ($args as $arg) {
            $matchers[] = self::hasItem($arg);
        }

        return AllOf::allOf($matchers);
    }
}
