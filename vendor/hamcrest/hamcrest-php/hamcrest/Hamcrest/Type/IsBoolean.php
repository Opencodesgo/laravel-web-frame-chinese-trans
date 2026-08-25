<?php
/**
 * Hamcrest，类型，是否布尔
 */

namespace Hamcrest\Type;

/*
 Copyright (c) 2010 hamcrest.org
 */
use Hamcrest\Core\IsTypeOf;

/**
 * Tests whether the value is a boolean.
 * 测试值是否为布尔值。
 */
class IsBoolean extends IsTypeOf
{

    /**
     * Creates a new instance of IsBoolean
     */
    public function __construct()
    {
        parent::__construct('boolean');
    }

    /**
     * Is the value a boolean?
     *
     * @factory boolValue
     */
    public static function booleanValue()
    {
        return new self;
    }
}
