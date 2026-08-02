<?php
/**
 * Illuminate，缓存，速率限制，无限制
 */

namespace Illuminate\Cache\RateLimiting;

class Unlimited extends GlobalLimit
{
    /**
     * Create a new limit instance.
	 * 创建新的限制实例
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct(PHP_INT_MAX);
    }
}
