<?php
/**
 * Illuminate，Session，基于缓存的会话处理程序
 */

namespace Illuminate\Session;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use SessionHandlerInterface;

class CacheBasedSessionHandler implements SessionHandlerInterface
{
    /**
     * The cache repository instance.
	 * 缓存存储库实例
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * The number of minutes to store the data in the cache.
	 * 将数据存储在缓存中的分钟数
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new cache driven handler instance.
	 * 创建一个新的缓存驱动处理程序实例
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @param  int  $minutes
     * @return void
     */
    public function __construct(CacheContract $cache, $minutes)
    {
        $this->cache = $cache;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function read($sessionId): string
    {
        return $this->cache->get($sessionId, '');
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        return $this->cache->put($sessionId, $data, $this->minutes * 60);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        return $this->cache->forget($sessionId);
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function gc($lifetime): int
    {
        return 0;
    }

    /**
     * Get the underlying cache repository.
	 * 获取底层缓存存储库
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function getCache()
    {
        return $this->cache;
    }
}
