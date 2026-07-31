<?php
/**
 * DeepCopy，DeepCopy
 */

namespace DeepCopy;

use function function_exists;

if (false === function_exists('DeepCopy\deep_copy')) {
    /**
     * Deep copies the given value.
	 * 深度复制给定的值
     *
     * @param mixed $value
     * @param bool  $useCloneMethod
     *
     * @return mixed
     */
    function deep_copy($value, $useCloneMethod = false)
    {
        return (new DeepCopy($useCloneMethod))->copy($value);
    }
}
