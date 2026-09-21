<?php
/**
 * Hamcrest，核心，AllOf
 */

namespace Hamcrest\Core;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\DiagnosingMatcher;
use Hamcrest\Util;

/**
 * Calculates the logical conjunction of multiple matchers. Evaluation is
 * shortcut, so subsequent matchers are not called if an earlier matcher
 * returns <code>false</code>.
 * 计算多个规的逻辑连词。评估是快捷方式,因此如果早期的matcher返回< code > false < / code >,则不会调用后续的matchers。
 */
class AllOf extends DiagnosingMatcher
{

    private $_matchers;

    public function __construct(array $matchers)
    {
        Util::checkAllAreMatchers($matchers);

        $this->_matchers = $matchers;
    }

    public function matchesWithDiagnosticDescription($item, Description $mismatchDescription)
    {
        /** @var $matcher \Hamcrest\Matcher */
        foreach ($this->_matchers as $matcher) {
            if (!$matcher->matches($item)) {
                $mismatchDescription->appendDescriptionOf($matcher)->appendText(' ');
                $matcher->describeMismatch($item, $mismatchDescription);

                return false;
            }
        }

        return true;
    }

    public function describeTo(Description $description)
    {
        $description->appendList('(', ' and ', ')', $this->_matchers);
    }

    /**
     * Evaluates to true only if ALL of the passed in matchers evaluate to true.
	 * 只有当所有的人都评估到真实的情况下,才会判断为真。
     *
     * @factory ...
     */
    public static function allOf(/* args... */)
    {
        $args = func_get_args();

        return new self(Util::createMatcherArray($args));
    }
}
