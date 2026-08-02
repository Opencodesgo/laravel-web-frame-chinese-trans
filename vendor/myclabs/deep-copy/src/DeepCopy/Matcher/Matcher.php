<?php
/**
 * DeepCopy，匹配程序，Matcher
 */

namespace DeepCopy\Matcher;

interface Matcher
{
    /**
     * @param object $object
     * @param string $property
     *
     * @return boolean
     */
    public function matches($object, $property);
}
