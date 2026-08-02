<?php
/**
 * Illuminate，队列，中间件，不重叠的
 */

namespace Illuminate\Queue\Middleware;

use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\InteractsWithTime;

class WithoutOverlapping
{
    use InteractsWithTime;

    /**
     * The job's unique key used for preventing overlaps.
	 * 作业的唯一密钥，用于防止重叠。
     *
     * @var string
     */
    public $key;

    /**
     * The number of seconds before a job should be available again if no lock was acquired.
	 * 如果未获得锁，则在作业再次可用之前的秒数。
     *
     * @var \DateTimeInterface|int|null
     */
    public $releaseAfter;

    /**
     * The number of seconds before the lock should expire.
	 * 锁到期前的秒数
     *
     * @var int
     */
    public $expiresAfter;

    /**
     * The prefix of the lock key.
	 * 锁键的前缀
     *
     * @var string
     */
    public $prefix = 'laravel-queue-overlap:';

    /**
     * Create a new middleware instance.
	 * 创建一个新的中间件实例
     *
     * @param  string  $key
     * @param  \DateTimeInterface|int|null  $releaseAfter
     * @param  \DateTimeInterface|int  $expiresAfter
     * @return void
     */
    public function __construct($key = '', $releaseAfter = 0, $expiresAfter = 0)
    {
        $this->key = $key;
        $this->releaseAfter = $releaseAfter;
        $this->expiresAfter = $this->secondsUntil($expiresAfter);
    }

    /**
     * Process the job.
	 * 处理作业
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        $lock = Container::getInstance()->make(Cache::class)->lock(
            $this->getLockKey($job), $this->expiresAfter
        );

        if ($lock->get()) {
            try {
                $next($job);
            } finally {
                $lock->release();
            }
        } elseif (! is_null($this->releaseAfter)) {
            $job->release($this->releaseAfter);
        }
    }

    /**
     * Set the delay (in seconds) to release the job back to the queue.
	 * 设置延迟（以秒为单位）以将作业释放回队列
     *
     * @param  \DateTimeInterface|int  $releaseAfter
     * @return $this
     */
    public function releaseAfter($releaseAfter)
    {
        $this->releaseAfter = $releaseAfter;

        return $this;
    }

    /**
     * Do not release the job back to the queue if no lock can be acquired.
	 * 如果无法获得锁，则不要将作业释放回队列。
     *
     * @return $this
     */
    public function dontRelease()
    {
        $this->releaseAfter = null;

        return $this;
    }

    /**
     * Set the maximum number of seconds that can elapse before the lock is released.
	 * 设置在释放锁之前可以经过的最大秒数
     *
     * @param  \DateTimeInterface|int  $expiresAfter
     * @return $this
     */
    public function expireAfter($expiresAfter)
    {
        $this->expiresAfter = $this->secondsUntil($expiresAfter);

        return $this;
    }

    /**
     * Set the prefix of the lock key.
	 * 设置锁定键的前缀
     *
     * @param  string  $prefix
     * @return $this
     */
    public function withPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the lock key for the given job.
	 * 得到给定工作的锁密钥
     *
     * @param  mixed  $job
     * @return string
     */
    public function getLockKey($job)
    {
        return $this->prefix.get_class($job).':'.$this->key;
    }
}
