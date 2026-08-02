<?php
/**
 * Illuminate，Http，资源，缺失值
 */

namespace Illuminate\Http\Resources;

class MissingValue implements PotentiallyMissing
{
    /**
     * Determine if the object should be considered "missing".
	 * 确定该对象是否应该被视为"丢失"
     *
     * @return bool
     */
    public function isMissing()
    {
        return true;
    }
}
