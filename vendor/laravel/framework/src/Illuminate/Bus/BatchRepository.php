<?php
/**
 * Illuminate，总线，批处理资源库
 */

namespace Illuminate\Bus;

use Closure;

interface BatchRepository
{
    /**
     * Retrieve a list of batches.
	 * 检索批列表
     *
     * @param  int  $limit
     * @param  mixed  $before
     * @return \Illuminate\Bus\Batch[]
     */
    public function get($limit, $before);

    /**
     * Retrieve information about an existing batch.
	 * 检索有关现有批处理的信息
     *
     * @param  string  $batchId
     * @return \Illuminate\Bus\Batch|null
     */
    public function find(string $batchId);

    /**
     * Store a new pending batch.
	 * 存储一个新的待处理批
     *
     * @param  \Illuminate\Bus\PendingBatch  $batch
     * @return \Illuminate\Bus\Batch
     */
    public function store(PendingBatch $batch);

    /**
     * Increment the total number of jobs within the batch.
	 * 增加批处理中的作业总数
     *
     * @param  string  $batchId
     * @param  int  $amount
     * @return void
     */
    public function incrementTotalJobs(string $batchId, int $amount);

    /**
     * Decrement the total number of pending jobs for the batch.
	 * 减少批处理的待处理作业总数
     *
     * @param  string  $batchId
     * @param  string  $jobId
     * @return \Illuminate\Bus\UpdatedBatchJobCounts
     */
    public function decrementPendingJobs(string $batchId, string $jobId);

    /**
     * Increment the total number of failed jobs for the batch.
	 * 增加批处理失败作业的总数
     *
     * @param  string  $batchId
     * @param  string  $jobId
     * @return \Illuminate\Bus\UpdatedBatchJobCounts
     */
    public function incrementFailedJobs(string $batchId, string $jobId);

    /**
     * Mark the batch that has the given ID as finished.
	 * 将具有给定ID的批标记为已完成
     *
     * @param  string  $batchId
     * @return void
     */
    public function markAsFinished(string $batchId);

    /**
     * Cancel the batch that has the given ID.
	 * 取消具有给定ID的批处理
     *
     * @param  string  $batchId
     * @return void
     */
    public function cancel(string $batchId);

    /**
     * Delete the batch that has the given ID.
	 * 删除具有给定ID的批处理
     *
     * @param  string  $batchId
     * @return void
     */
    public function delete(string $batchId);

    /**
     * Execute the given Closure within a storage specific transaction.
	 * 在特定于存储的事务中执行给定的Closure
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    public function transaction(Closure $callback);
}
