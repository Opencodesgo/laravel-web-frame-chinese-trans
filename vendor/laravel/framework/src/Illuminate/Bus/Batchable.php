<?php
/**
 * Illuminate，总线，可批处理的
 */

namespace Illuminate\Bus;

use Illuminate\Container\Container;

trait Batchable
{
    /**
     * The batch ID (if applicable).
	 * 批处理ID（如果适用）
     *
     * @var string
     */
    public $batchId;

    /**
     * Get the batch instance for the job, if applicable.
	 * 获取作业的批处理实例（如果适用）
     *
     * @return \Illuminate\Bus\Batch|null
     */
    public function batch()
    {
        if ($this->batchId) {
            return Container::getInstance()->make(BatchRepository::class)->find($this->batchId);
        }
    }

    /**
     * Determine if the batch is still active and processing.
	 * 确定批处理是否仍处于活动状态和处理状态
     *
     * @return bool
     */
    public function batching()
    {
        $batch = $this->batch();

        return $batch && ! $batch->cancelled();
    }

    /**
     * Set the batch ID on the job.
	 * 在任务上设置批ID
     *
     * @param  string  $batchId
     * @return $this
     */
    public function withBatchId(string $batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }
}
