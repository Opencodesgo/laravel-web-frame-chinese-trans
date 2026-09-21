<?php
/**
 * DeepCopy，匹配程序，匹配程序
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
