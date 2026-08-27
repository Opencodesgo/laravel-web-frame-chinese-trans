<?php
/**
 * Illuminate，缓存，Cache锁
 */

namespace Illuminate\Cache;

class CacheLock extends Lock
{
    /**
     * The cache store implementation.
	 * 缓存存储实现
     *
     * @var \Illuminate\Contracts\Cache\Store
     */
    protected $store;

    /**
     * Create a new lock instance.
	 * 创建新的锁定实例
     *
     * @param  \Illuminate\Contracts\Cache\Store  $store
     * @param  string  $name
     * @param  int  $seconds
     * @param  string|null  $owner
     * @return void
     */
    public function __construct($store, $name, $seconds, $owner = null)
    {
        parent::__construct($name, $seconds, $owner);

        $this->store = $store;
    }

    /**
     * Attempt to acquire the lock.
	 * 尝试获取锁
     *
     * @return bool
     */
    public function acquire()
    {
        if (method_exists($this->store, 'add') && $this->seconds > 0) {
            return $this->store->add(
                $this->name, $this->owner, $this->seconds
            );
        }

        if (! is_null($this->store->get($this->name))) {
            return false;
        }

        return ($this->seconds > 0)
                ? $this->store->put($this->name, $this->owner, $this->seconds)
                : $this->store->forever($this->name, $this->owner);
    }

    /**
     * Release the lock.
	 * 释放锁
     *
     * @return bool
     */
    public function release()
    {
        if ($this->isOwnedByCurrentProcess()) {
            return $this->store->forget($this->name);
        }

        return false;
    }

    /**
     * Releases this lock regardless of ownership.
	 * 释放此锁，无论其所有权如何。
     *
     * @return void
     */
    public function forceRelease()
    {
        $this->store->forget($this->name);
    }

    /**
     * Returns the owner value written into the driver for this lock.
	 * 返回写入此锁的驱动程序的所有者值
     *
     * @return mixed
     */
    protected function getCurrentOwner()
    {
        return $this->store->get($this->name);
    }
}
