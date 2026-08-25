<?php
/**
 * Hamcrest，核心，Is Not
 */

namespace Hamcrest\Core;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\BaseMatcher;
use Hamcrest\Description;
use Hamcrest\Matcher;
use Hamcrest\Util;

/**
 * Calculates the logical negation of a matcher.
 * 计算匹配器的逻辑否定。
 */
class IsNot extends BaseMatcher
{

    private $_matcher;

    public function __construct(Matcher $matcher)
    {
        $this->_matcher = $matcher;
    }

    public function matches($arg)
    {
        return !$this->_matcher->matches($arg);
    }

    public function describeTo(Description $description)
    {
        $description->appendText('not ')->appendDescriptionOf($this->_matcher);
    }

    /**
     * Matches if value does not match $value.
	 * 如果value不匹配$value，则匹配。
     *
     * @factory
     */
    public static function not($value)
    {
        return new self(Util::wrapValueWithIsEqual($value));
    }
}
