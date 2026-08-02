<?php
/**
 * Hamcrest，核心，Any Of
 */

namespace Hamcrest\Core;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\Util;

/**
 * Calculates the logical disjunction of multiple matchers. Evaluation is
 * shortcut, so subsequent matchers are not called if an earlier matcher
 * returns <code>true</code>.
 * 计算多个匹配器的逻辑析取。评估是快捷方式，因此如果前面的匹配器返回 <code>true</code>，后续的匹配器将不会被调用。
 */
class AnyOf extends ShortcutCombination
{

    public function __construct(array $matchers)
    {
        parent::__construct($matchers);
    }

    public function matches($item)
    {
        return $this->matchesWithShortcut($item, true);
    }

    public function describeTo(Description $description)
    {
        $this->describeToWithOperator($description, 'or');
    }

    /**
     * Evaluates to true if ANY of the passed in matchers evaluate to true.
	 * 如果传入的匹配器中的任何一个求值为真，则求值为真。
     *
     * @factory ...
     */
    public static function anyOf(/* args... */)
    {
        $args = func_get_args();

        return new self(Util::createMatcherArray($args));
    }

    /**
     * Evaluates to false if ANY of the passed in matchers evaluate to true.
     *
     * @factory ...
     */
    public static function noneOf(/* args... */)
    {
        $args = func_get_args();

        return IsNot::not(
            new self(Util::createMatcherArray($args))
        );
    }
}
