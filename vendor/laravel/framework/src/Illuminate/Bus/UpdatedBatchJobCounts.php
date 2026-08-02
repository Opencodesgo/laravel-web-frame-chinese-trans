<?php
/**
 * Illuminate，总线，更新批处理作业计数
 */

namespace Illuminate\Bus;

class UpdatedBatchJobCounts
{
    /**
     * The number of pending jobs remaining for the batch.
	 * 批处理中剩余的待处理作业的数量
     *
     * @var int
     */
    public $pendingJobs;

    /**
     * The number of failed jobs that belong to the batch.
	 * 属于批处理的失败作业的个数
     *
     * @var int
     */
    public $failedJobs;

    /**
     * Create a new batch job counts object.
	 * 创建一个新的批处理作业计数对象
     *
     * @param  int  $pendingJobs
     * @param  int  $failedJobs
     * @return void
     */
    public function __construct(int $pendingJobs = 0, int $failedJobs = 0)
    {
        $this->pendingJobs = $pendingJobs;
        $this->failedJobs = $failedJobs;
    }

    /**
     * Determine if all jobs have run exactly once.
	 * 确定所有作业是否只运行过一次
     *
     * @return bool
     */
    public function allJobsHaveRanExactlyOnce()
    {
        return ($this->pendingJobs - $this->failedJobs) === 0;
    }
}
