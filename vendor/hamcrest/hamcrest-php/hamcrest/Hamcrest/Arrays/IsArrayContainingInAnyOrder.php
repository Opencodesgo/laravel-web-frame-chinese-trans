<?php
/**
 * Hamcrest，数组，数组是否包含（顺序无关）
 */

namespace Hamcrest\Arrays;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\TypeSafeDiagnosingMatcher;
use Hamcrest\Util;

/**
 * Matches if an array contains a set of items satisfying nested matchers.
 * 如果数组包含一组满足嵌套匹配器的项，则匹配。
 */
class IsArrayContainingInAnyOrder extends TypeSafeDiagnosingMatcher
{

    private $_elementMatchers;

    public function __construct(array $elementMatchers)
    {
        parent::__construct(self::TYPE_ARRAY);

        Util::checkAllAreMatchers($elementMatchers);

        $this->_elementMatchers = $elementMatchers;
    }

    protected function matchesSafelyWithDiagnosticDescription($array, Description $mismatchDescription)
    {
        $matching = new MatchingOnce($this->_elementMatchers, $mismatchDescription);

        foreach ($array as $element) {
            if (!$matching->matches($element)) {
                return false;
            }
        }

        return $matching->isFinished($array);
    }

    public function describeTo(Description $description)
    {
        $description->appendList('[', ', ', ']', $this->_elementMatchers)
                                ->appendText(' in any order')
                                ;
    }

    /**
     * An array with elements that match the given matchers.
	 * 包含与给定匹配器匹配的元素的数组
     *
     * @factory containsInAnyOrder ...
     */
    public static function arrayContainingInAnyOrder(/* args... */)
    {
        $args = func_get_args();

        return new self(Util::createMatcherArray($args));
    }
}
