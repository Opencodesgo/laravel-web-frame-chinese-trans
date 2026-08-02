<?php
/**
 * Illuminate，缓存，无锁
 */

namespace Illuminate\Cache;

class NoLock extends Lock
{
    /**
     * Attempt to acquire the lock.
	 * 尝试获取锁
     *
     * @return bool
     */
    public function acquire()
    {
        return true;
    }

    /**
     * Release the lock.
	 * 释放锁
     *
     * @return bool
     */
    public function release()
    {
        return true;
    }

    /**
     * Releases this lock in disregard of ownership.
	 * 释放此锁，而不考虑所有权。
     *
     * @return void
     */
    public function forceRelease()
    {
        //
    }

    /**
     * Returns the owner value written into the driver for this lock.
	 * 返回写入此锁的驱动程序的所有者值
     *
     * @return mixed
     */
    protected function getCurrentOwner()
    {
        return $this->owner;
    }
}
