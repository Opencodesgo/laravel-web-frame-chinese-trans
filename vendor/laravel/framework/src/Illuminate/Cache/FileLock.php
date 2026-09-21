<?php
/**
 * Illuminate，缓存，文件锁
 */

namespace Illuminate\Cache;

class FileLock extends CacheLock
{
    /**
     * Attempt to acquire the lock.
	 * 尝试获取锁
     *
     * @return bool
     */
    public function acquire()
    {
        return $this->store->add($this->name, $this->owner, $this->seconds);
    }
}
