<?php
/**
 * Illuminate，控制台，线程调度，缓存感知
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
