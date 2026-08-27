<?php
/**
 * Illuminate，总线，唯一锁
 */

namespace Illuminate\Bus;

use Illuminate\Contracts\Cache\Repository as Cache;

class UniqueLock
{
    /**
     * The cache repository implementation.
	 * 缓存存储库实现
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Create a new unique lock manager instance.
	 * 创建一个新的唯一锁管理器实例
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Attempt to acquire a lock for the given job.
	 * 尝试为给定的作业获取锁
     *
     * @param  mixed  $job
     * @return bool
     */
    public function acquire($job)
    {
        $uniqueFor = method_exists($job, 'uniqueFor')
                    ? $job->uniqueFor()
                    : ($job->uniqueFor ?? 0);

        $cache = method_exists($job, 'uniqueVia')
                    ? $job->uniqueVia()
                    : $this->cache;

        return (bool) $cache->lock($this->getKey($job), $uniqueFor)->get();
    }

    /**
     * Release the lock for the given job.
	 * 释放给定作业的锁
     *
     * @param  mixed  $job
     * @return void
     */
    public function release($job)
    {
        $cache = method_exists($job, 'uniqueVia')
                    ? $job->uniqueVia()
                    : $this->cache;

        $cache->lock($this->getKey($job))->forceRelease();
    }

    /**
     * Generate the lock key for the given job.
	 * 为给定的作业生成锁密钥
     *
     * @param  mixed  $job
     * @return string
     */
    protected function getKey($job)
    {
        $uniqueId = method_exists($job, 'uniqueId')
                    ? $job->uniqueId()
                    : ($job->uniqueId ?? '');

        return 'laravel_unique_job:'.get_class($job).$uniqueId;
    }
}
