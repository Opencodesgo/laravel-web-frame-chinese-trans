<?php
/**
 * Hamcrest，文本，字符串按顺序包含
 */

namespace Hamcrest\Text;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\TypeSafeMatcher;

/**
 * Tests if the value contains a series of substrings in a constrained order.
 * 测试值是否包含按约束顺序排列的一系列子字符串。
 */
class StringContainsInOrder extends TypeSafeMatcher
{

    private $_substrings;

    public function __construct(array $substrings)
    {
        parent::__construct(self::TYPE_STRING);

        $this->_substrings = $substrings;
    }

    protected function matchesSafely($item)
    {
        $fromIndex = 0;

        foreach ($this->_substrings as $substring) {
            if (false === $fromIndex = strpos($item, $substring, $fromIndex)) {
                return false;
            }
        }

        return true;
    }

    protected function describeMismatchSafely($item, Description $mismatchDescription)
    {
        $mismatchDescription->appendText('was ')->appendText($item);
    }

    public function describeTo(Description $description)
    {
        $description->appendText('a string containing ')
                                ->appendValueList('', ', ', '', $this->_substrings)
                                ->appendText(' in order')
                                ;
    }

    /**
     * Matches if value contains $substrings in a constrained order.
	 * 如果value包含$substrings，则匹配。
     *
     * @factory ...
     */
    public static function stringContainsInOrder(/* args... */)
    {
        $args = func_get_args();

        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }

        return new self($args);
    }
}
