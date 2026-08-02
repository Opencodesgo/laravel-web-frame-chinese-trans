<?php
/**
 * Illuminate，缓存，锁抽象类
 */

namespace Illuminate\Cache;

use Illuminate\Contracts\Cache\Lock as LockContract;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Str;

abstract class Lock implements LockContract
{
    use InteractsWithTime;

    /**
     * The name of the lock.
	 * 锁的名称
     *
     * @var string
     */
    protected $name;

    /**
     * The number of seconds the lock should be maintained.
	 * 应该维护锁的秒数
     *
     * @var int
     */
    protected $seconds;

    /**
     * The scope identifier of this lock.
	 * 此锁的作用域标识符
     *
     * @var string
     */
    protected $owner;

    /**
     * The number of milliseconds to wait before re-attempting to acquire a lock while blocking.
	 * 在阻塞时重新尝试获取锁之前等待的毫秒数
     *
     * @var int
     */
    protected $sleepMilliseconds = 250;

    /**
     * Create a new lock instance.
	 * 创建新的锁实例
     *
     * @param  string  $name
     * @param  int  $seconds
     * @param  string|null  $owner
     * @return void
     */
    public function __construct($name, $seconds, $owner = null)
    {
        if (is_null($owner)) {
            $owner = Str::random();
        }

        $this->name = $name;
        $this->owner = $owner;
        $this->seconds = $seconds;
    }

    /**
     * Attempt to acquire the lock.
	 * 尝试获取锁
     *
     * @return bool
     */
    abstract public function acquire();

    /**
     * Release the lock.
	 * 释放锁
     *
     * @return bool
     */
    abstract public function release();

    /**
     * Returns the owner value written into the driver for this lock.
	 * 返回写入此锁的驱动程序的所有者值
     *
     * @return string
     */
    abstract protected function getCurrentOwner();

    /**
     * Attempt to acquire the lock.
	 * 尝试获取锁
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function get($callback = null)
    {
        $result = $this->acquire();

        if ($result && is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }

        return $result;
    }

    /**
     * Attempt to acquire the lock for the given number of seconds.
	 * 尝试在给定的秒数内获取锁
     *
     * @param  int  $seconds
     * @param  callable|null  $callback
     * @return bool
     *
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException
     */
    public function block($seconds, $callback = null)
    {
        $starting = $this->currentTime();

        while (! $this->acquire()) {
            usleep($this->sleepMilliseconds * 1000);

            if ($this->currentTime() - $seconds >= $starting) {
                throw new LockTimeoutException;
            }
        }

        if (is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }

        return true;
    }

    /**
     * Returns the current owner of the lock.
	 * 返回锁的当前所有者
     *
     * @return string
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * Determines whether this lock is allowed to release the lock in the driver.
	 * 确定是否允许此锁释放驱动程序中的锁
     *
     * @return bool
     */
    protected function isOwnedByCurrentProcess()
    {
        return $this->getCurrentOwner() === $this->owner;
    }

    /**
     * Specify the number of milliseconds to sleep in between blocked lock aquisition attempts.
	 * 指定被阻塞的锁获取尝试之间的睡眠毫秒数
     *
     * @param  int  $milliseconds
     * @return $this
     */
    public function betweenBlockedAttemptsSleepFor($milliseconds)
    {
        $this->sleepMilliseconds = $milliseconds;

        return $this;
    }
}
