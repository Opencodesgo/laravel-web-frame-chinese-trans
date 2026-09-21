<?php
/**
 * Illuminate，缓存，数据库锁
 */

namespace Illuminate\Cache;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class DatabaseLock extends Lock
{
    /**
     * The database connection instance.
	 * 数据库连接实例
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * The database table name.
	 * 数据库表名
     *
     * @var string
     */
    protected $table;

    /**
     * The prune probability odds.
	 * 剪枝概率
     *
     * @var array
     */
    protected $lottery;

    /**
     * Create a new lock instance.
	 * 创建一个新的锁实例
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  string  $table
     * @param  string  $name
     * @param  int  $seconds
     * @param  string|null  $owner
     * @param  array  $lottery
     * @return void
     */
    public function __construct(Connection $connection, $table, $name, $seconds, $owner = null, $lottery = [2, 100])
    {
        parent::__construct($name, $seconds, $owner);

        $this->connection = $connection;
        $this->table = $table;
        $this->lottery = $lottery;
    }

    /**
     * Attempt to acquire the lock.
	 * 尝试获取锁
     *
     * @return bool
     */
    public function acquire()
    {
        try {
            $this->connection->table($this->table)->insert([
                'key' => $this->name,
                'owner' => $this->owner,
                'expiration' => $this->expiresAt(),
            ]);

            $acquired = true;
        } catch (QueryException $e) {
            $updated = $this->connection->table($this->table)
                ->where('key', $this->name)
                ->where(function ($query) {
                    return $query->where('owner', $this->owner)->orWhere('expiration', '<=', time());
                })->update([
                    'owner' => $this->owner,
                    'expiration' => $this->expiresAt(),
                ]);

            $acquired = $updated >= 1;
        }

        if (random_int(1, $this->lottery[1]) <= $this->lottery[0]) {
            $this->connection->table($this->table)->where('expiration', '<=', time())->delete();
        }

        return $acquired;
    }

    /**
     * Get the UNIX timestamp indicating when the lock should expire.
	 * 获取指示锁何时到期的UNIX时间戳
     *
     * @return int
     */
    protected function expiresAt()
    {
        return $this->seconds > 0 ? time() + $this->seconds : Carbon::now()->addDays(1)->getTimestamp();
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
            $this->connection->table($this->table)
                        ->where('key', $this->name)
                        ->where('owner', $this->owner)
                        ->delete();

            return true;
        }

        return false;
    }

    /**
     * Releases this lock in disregard of ownership.
	 * 释放此锁，而不考虑所有权。
     *
     * @return void
     */
    public function forceRelease()
    {
        $this->connection->table($this->table)
                    ->where('key', $this->name)
                    ->delete();
    }

    /**
     * Returns the owner value written into the driver for this lock.
	 * 返回写入此锁的驱动程序的所有者值
     *
     * @return string
     */
    protected function getCurrentOwner()
    {
        return optional($this->connection->table($this->table)->where('key', $this->name)->first())->owner;
    }

    /**
     * Get the name of the database connection being used to manage the lock.
	 * 获取用于管理锁的数据库连接的名称
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connection->getName();
    }
}
