<?php
/**
 * Illuminate，基础，缓存基本维护模式
 */

namespace Illuminate\Foundation;

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Foundation\MaintenanceMode;

class CacheBasedMaintenanceMode implements MaintenanceMode
{
    /**
     * The cache factory.
	 * 缓存工厂
     *
     * @var \Illuminate\Contracts\Cache\Factory
     */
    protected $cache;

    /**
     * The cache store that should be utilized.
	 * 应该使用的缓存存储
     *
     * @var string
     */
    protected $store;

    /**
     * The cache key to use when storing maintenance mode information.
	 * 存储维护模式信息时要使用的缓存键
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new cache based maintenance mode implementation.
	 * 创建一个新的基于缓存的维护模式实现
     *
     * @param  \Illuminate\Contracts\Cache\Factory  $cache
     * @param  string  $store
     * @param  string  $key
     * @return void
     */
    public function __construct(Factory $cache, string $store, string $key)
    {
        $this->cache = $cache;
        $this->store = $store;
        $this->key = $key;
    }

    /**
     * Take the application down for maintenance.
	 * 删除应用程序以进行维护
     *
     * @param  array  $payload
     * @return void
     */
    public function activate(array $payload): void
    {
        $this->getStore()->put($this->key, $payload);
    }

    /**
     * Take the application out of maintenance.
	 * 将应用程序从维护中移除
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->getStore()->forget($this->key);
    }

    /**
     * Determine if the application is currently down for maintenance.
	 * 确定应用程序当前是否关闭以进行维护
     *
     * @return bool
     */
    public function active(): bool
    {
        return $this->getStore()->has($this->key);
    }

    /**
     * Get the data array which was provided when the application was placed into maintenance.
	 * 获取应用程序进入维护状态时提供的数据数组
     *
     * @return array
     */
    public function data(): array
    {
        return $this->getStore()->get($this->key);
    }

    /**
     * Get the cache store to use.
	 * 获取要使用的缓存存储
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function getStore(): Repository
    {
        return $this->cache->store($this->store);
    }
}
