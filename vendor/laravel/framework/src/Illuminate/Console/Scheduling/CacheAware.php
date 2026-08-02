<?php
/**
 * Illuminate，控制台，调度，CacheAware
 */

namespace Illuminate\Console\Scheduling;

interface CacheAware
{
    /**
     * Specify the cache store that should be used.
	 * 指定应该使用的缓存存储
     *
     * @param  string  $store
     * @return $this
     */
    public function useStore($store);
}
