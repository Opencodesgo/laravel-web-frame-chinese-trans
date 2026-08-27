<?php
/**
 * Illuminate，总线，可批处理的
 */

namespace Illuminate\Bus;

use Carbon\CarbonImmutable;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Support\Testing\Fakes\BatchFake;

trait Batchable
{
    /**
     * The batch ID (if applicable).
	 * 批ID（如果适用）
     *
     * @var string
     */
    public $batchId;

    /**
     * The fake batch, if applicable.
	 * 假批次，如适用。
     *
     * @var \Illuminate\Support\Testing\Fakes\BatchFake
     */
    private $fakeBatch;

    /**
     * Get the batch instance for the job, if applicable.
	 * 获取作业的批处理实例（如果适用）
     *
     * @return \Illuminate\Bus\Batch|null
     */
    public function batch()
    {
        if ($this->fakeBatch) {
            return $this->fakeBatch;
        }

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
	 * 在作业上设置批ID
     *
     * @param  string  $batchId
     * @return $this
     */
    public function withBatchId(string $batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Indicate that the job should use a fake batch.
	 * 说明作业应该使用假批处理
     *
     * @param  string  $id
     * @param  string  $name
     * @param  int  $totalJobs
     * @param  int  $pendingJobs
     * @param  int  $failedJobs
     * @param  array  $failedJobIds
     * @param  array  $options
     * @param  \Carbon\CarbonImmutable  $createdAt
     * @param  \Carbon\CarbonImmutable|null  $cancelledAt
     * @param  \Carbon\CarbonImmutable|null  $finishedAt
     * @return array{0: $this, 1: \Illuminate\Support\Testing\Fakes\BatchFake}
     */
    public function withFakeBatch(string $id = '',
                                  string $name = '',
                                  int $totalJobs = 0,
                                  int $pendingJobs = 0,
                                  int $failedJobs = 0,
                                  array $failedJobIds = [],
                                  array $options = [],
                                  CarbonImmutable $createdAt = null,
                                  ?CarbonImmutable $cancelledAt = null,
                                  ?CarbonImmutable $finishedAt = null)
    {
        $this->fakeBatch = new BatchFake(
            empty($id) ? (string) Str::uuid() : $id,
            $name,
            $totalJobs,
            $pendingJobs,
            $failedJobs,
            $failedJobIds,
            $options,
            $createdAt ?? CarbonImmutable::now(),
            $cancelledAt,
            $finishedAt,
        );

        return [$this, $this->fakeBatch];
    }
}
