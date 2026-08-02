<?php
/**
 * Illuminate，契约，队列，作业
 */

namespace Illuminate\Contracts\Queue;

interface Job
{
    /**
     * Get the UUID of the job.
	 * 获取作业的UUID
     *
     * @return string|null
     */
    public function uuid();

    /**
     * Get the job identifier.
	 * 获取作业标识符
     *
     * @return string
     */
    public function getJobId();

    /**
     * Get the decoded body of the job.
	 * 得到作业的解密主体
     *
     * @return array
     */
    public function payload();

    /**
     * Fire the job.
	 * 触发作业
     *
     * @return void
     */
    public function fire();

    /**
     * Release the job back into the queue.
	 * 将作业释放回队列
     *
     * Accepts a delay specified in seconds.
	 * 接受以秒为单位指定的延迟
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0);

    /**
     * Determine if the job was released back into the queue.
	 * 确定任务是否被释放回队列
     *
     * @return bool
     */
    public function isReleased();

    /**
     * Delete the job from the queue.
	 * 从队业中删除任务
     *
     * @return void
     */
    public function delete();

    /**
     * Determine if the job has been deleted.
	 * 确定任务是否已删除
     *
     * @return bool
     */
    public function isDeleted();

    /**
     * Determine if the job has been deleted or released.
	 * 确定任务是否已被删除或释放
     *
     * @return bool
     */
    public function isDeletedOrReleased();

    /**
     * Get the number of times the job has been attempted.
	 * 获取该任务被尝试的次数
     *
     * @return int
     */
    public function attempts();

    /**
     * Determine if the job has been marked as a failure.
	 * 确定任务是否已被标记为失败
     *
     * @return bool
     */
    public function hasFailed();

    /**
     * Mark the job as "failed".
	 * 标记任务为"失败"
     *
     * @return void
     */
    public function markAsFailed();

    /**
     * Delete the job, call the "failed" method, and raise the failed job event.
	 * 删除作业，调用"failed"方法，并引发失败的作业事件。
     *
     * @param  \Throwable|null  $e
     * @return void
     */
    public function fail($e = null);

    /**
     * Get the number of times to attempt a job.
	 * 获取尝试某项工作的次数
     *
     * @return int|null
     */
    public function maxTries();

    /**
     * Get the maximum number of exceptions allowed, regardless of attempts.
	 * 获取允许的最大异常数，无论尝试次数如何。
     *
     * @return int|null
     */
    public function maxExceptions();

    /**
     * Get the number of seconds the job can run.
	 * 获取作业可以运行的秒数
     *
     * @return int|null
     */
    public function timeout();

    /**
     * Get the timestamp indicating when the job should timeout.
	 * 获取指示作业何时应该超时的时间戳
     *
     * @return int|null
     */
    public function timeoutAt();

    /**
     * Get the name of the queued job class.
	 * 获取排队作业类的名称
     *
     * @return string
     */
    public function getName();

    /**
     * Get the resolved name of the queued job class.
	 * 获取排队作业类的解析名称
     *
     * Resolves the name of "wrapped" jobs such as class-based handlers.
     *
     * @return string
     */
    public function resolveName();

    /**
     * Get the name of the connection the job belongs to.
	 * 获取作业所属的连接的名称
     *
     * @return string
     */
    public function getConnectionName();

    /**
     * Get the name of the queue the job belongs to.
	 * 获取作业所属队列的名称
     *
     * @return string
     */
    public function getQueue();

    /**
     * Get the raw body string for the job.
	 * 获取工作的原始主体字符串
     *
     * @return string
     */
    public function getRawBody();
}
