<?php
/**
 * Hamcrest，自我描述
 */

namespace Hamcrest;

/*
 Copyright (c) 2009 hamcrest.org
 */

/**
 * The ability of an object to describe itself.
 * 物体描述自身的能力。
 */
interface SelfDescribing
{

    /**
     * Generates a description of the object.  The description may be part
     * of a description of a larger object of which this is just a component,
     * so it should be worded appropriately.
	 * 生成对象的描述。描述可能是对一个更大物体的说明，而该物体只是其中的一部分，因此应适当表述。
     *
     * @param \Hamcrest\Description $description
     *   The description to be built or appended to.
     */
    public function describeTo(Description $description);
}
