<?php
/**
 * Hamcrest，类型，是可调用的
 */

namespace Hamcrest\Type;

/*
 Copyright (c) 2010 hamcrest.org
 */
use Hamcrest\Core\IsTypeOf;

/**
 * Tests whether the value is callable.
 * 测试该值是否可调用。
 */
class IsCallable extends IsTypeOf
{

    /**
     * Creates a new instance of IsCallable
	 * 创建一个新的IsCallable实例
     */
    public function __construct()
    {
        parent::__construct('callable');
    }

    public function matches($item)
    {
        return is_callable($item);
    }

    /**
     * Is the value callable?
     *
     * @factory
     */
    public static function callableValue()
    {
        return new self;
    }
}
