<?php
/**
 * Hamcrest，内部的，自我描述值
 */

namespace Hamcrest\Internal;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Description;
use Hamcrest\SelfDescribing;

/**
 * A wrapper around any value so that it describes itself.
 * 一个围绕任何值的包装器,以便它描述自己。
 */
class SelfDescribingValue implements SelfDescribing
{

    private $_value;

    public function __construct($value)
    {
        $this->_value = $value;
    }

    public function describeTo(Description $description)
    {
        $description->appendValue($this->_value);
    }
}
