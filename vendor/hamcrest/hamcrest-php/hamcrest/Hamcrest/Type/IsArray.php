<?php
/**
 * Hamcrest，类型，是否数组
 */

namespace Hamcrest\Type;

/*
 Copyright (c) 2010 hamcrest.org
 */
use Hamcrest\Core\IsTypeOf;

/**
 * Tests whether the value is an array.
 * 测试值是否为数组。
 */
class IsArray extends IsTypeOf
{

    /**
     * Creates a new instance of IsArray
	 * 创建一个新的IsArray实例
     */
    public function __construct()
    {
        parent::__construct('array');
    }

    /**
     * Is the value an array?
     *
     * @factory
     */
    public static function arrayValue()
    {
        return new self;
    }
}
