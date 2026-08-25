<?php
/**
 * Hamcrest，核心，Is Same
 */

namespace Hamcrest\Core;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\BaseMatcher;
use Hamcrest\Description;

/**
 * Is the value the same object as another value?
 * In PHP terms, does $a === $b?
 * 值是否与另一个值是相同的对象？
 */
class IsSame extends BaseMatcher
{

    private $_object;

    public function __construct($object)
    {
        $this->_object = $object;
    }

    public function matches($object)
    {
        return ($object === $this->_object) && ($this->_object === $object);
    }

    public function describeTo(Description $description)
    {
        $description->appendText('sameInstance(')
                                ->appendValue($this->_object)
                                ->appendText(')')
                                ;
    }

    /**
     * Creates a new instance of IsSame.
	 * 创建IsSame的新实例
     *
     * @param mixed $object
     *   The predicate evaluates to true only when the argument is
     *   this object.
     *
     * @return \Hamcrest\Core\IsSame
     * @factory
     */
    public static function sameInstance($object)
    {
        return new self($object);
    }
}
