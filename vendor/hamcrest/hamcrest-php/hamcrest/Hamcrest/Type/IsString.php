<?php
/**
 * Hamcrest，类型，是否字符串
 */

namespace Hamcrest\Type;

/*
 Copyright (c) 2010 hamcrest.org
 */
use Hamcrest\Core\IsTypeOf;

/**
 * Tests whether the value is a string.
 * 测试值是否为字符串。
 */
class IsString extends IsTypeOf
{

    /**
     * Creates a new instance of IsString
	 * 创建一个新的IsString实例
     */
    public function __construct()
    {
        parent::__construct('string');
    }

    /**
     * Is the value a string?
     *
     * @factory
     */
    public static function stringValue()
    {
        return new self;
    }
}
