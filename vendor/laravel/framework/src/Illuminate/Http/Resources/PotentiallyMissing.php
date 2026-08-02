<?php
/**
 * Illuminate，Http，资源，可能会丢失
 */

namespace Illuminate\Http\Resources;

interface PotentiallyMissing
{
    /**
     * Determine if the object should be considered "missing".
	 * 确定该对象是否应该被视为"丢失"
     *
     * @return bool
     */
    public function isMissing();
}
