<?php
/**
 * Hamcrest，核心，是否相等
 */

namespace Hamcrest\Core;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\BaseMatcher;
use Hamcrest\Description;

/**
 * Is the value equal to another value, as tested by the use of the "=="
 * comparison operator?
 * 值是否等于另一个值,就像使用"=="比较操作符一样?
 */
class IsEqual extends BaseMatcher
{

    private $_item;

    public function __construct($item)
    {
        $this->_item = $item;
    }

    public function matches($arg)
    {
        return (($arg == $this->_item) && ($this->_item == $arg));
    }

    public function describeTo(Description $description)
    {
        $description->appendValue($this->_item);
    }

    /**
     * Is the value equal to another value, as tested by the use of the "=="
     * comparison operator?
     *
     * @factory
     */
    public static function equalTo($item)
    {
        return new self($item);
    }
}
