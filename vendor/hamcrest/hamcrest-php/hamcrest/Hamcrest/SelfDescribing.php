<?php
/**
 * Hamcrest，自描述性
 */

namespace Hamcrest;

/*
 Copyright (c) 2009 hamcrest.org
 */

/**
 * The ability of an object to describe itself.
 * 对象描述自身的能力。
 */
interface SelfDescribing
{

    /**
     * Generates a description of the object.  The description may be part
     * of a description of a larger object of which this is just a component,
     * so it should be worded appropriately.
	 * 生成对象的描述。描述可能是描述一个更大的对象的一部分,它只是一个组件,所以它应该被适当地处理。
     *
     * @param \Hamcrest\Description $description
     *   The description to be built or appended to.
     */
    public function describeTo(Description $description);
}
