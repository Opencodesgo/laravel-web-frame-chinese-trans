<?php
/**
 * Illuminate，缓存，速率限制，全局限制
 */

namespace Illuminate\Cache\RateLimiting;

class GlobalLimit extends Limit
{
    /**
     * Create a new limit instance.
	 * 创建新的限制实例
     *
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return void
     */
    public function __construct(int $maxAttempts, int $decayMinutes = 1)
    {
        parent::__construct('', $maxAttempts, $decayMinutes);
    }
}
