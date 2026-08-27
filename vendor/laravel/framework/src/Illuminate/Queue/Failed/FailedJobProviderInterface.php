<?php
/**
 * Illuminate，队列，失败，失败的作业提供程序接口
 */

namespace Illuminate\Queue\Failed;

interface FailedJobProviderInterface
{
    /**
     * Log a failed job into storage.
	 * 记录失败的作业到存储中
     *
     * @param  string  $connection
     * @param  string  $queue
     * @param  string  $payload
     * @param  \Throwable  $exception
     * @return string|int|null
     */
    public function log($connection, $queue, $payload, $exception);

    /**
     * Get a list of all of the failed jobs.
	 * 获取所有失败任务的列表
     *
     * @return array
     */
    public function all();

    /**
     * Get a single failed job.
	 * 得到单个失败作业
     *
     * @param  mixed  $id
     * @return object|null
     */
    public function find($id);

    /**
     * Delete a single failed job from storage.
	 * 从存储中删除单个失败的作业
     *
     * @param  mixed  $id
     * @return bool
     */
    public function forget($id);

    /**
     * Flush all of the failed jobs from storage.
	 * 从存储中清除所有失败的作业
     *
     * @param  int|null  $hours
     * @return void
     */
    public function flush($hours = null);
}
