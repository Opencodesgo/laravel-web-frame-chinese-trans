<?php
/**
 * Illuminate，总线，批处理
 */

namespace Illuminate\Bus;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonSerializable;
use Throwable;

class Batch implements Arrayable, JsonSerializable
{
    /**
     * The queue factory implementation.
	 * 队列工厂实现
     *
     * @var \Illuminate\Contracts\Queue\Factory
     */
    protected $queue;

    /**
     * The repository implementation.
	 * 资源库实现
     *
     * @var \Illuminate\Bus\BatchRepository
     */
    protected $repository;

    /**
     * The batch ID.
	 * 批处理ID
     *
     * @var string
     */
    public $id;

    /**
     * The batch name.
	 * 批处理名称
     *
     * @var string
     */
    public $name;

    /**
     * The total number of jobs that belong to the batch.
	 * 属于批处理的作业总数
     *
     * @var int
     */
    public $totalJobs;

    /**
     * The total number of jobs that are still pending.
	 * 仍在等待中的作业总数
     *
     * @var int
     */
    public $pendingJobs;

    /**
     * The total number of jobs that have failed.
	 * 失败的作业总数
     *
     * @var int
     */
    public $failedJobs;

    /**
     * The IDs of the jobs that have failed.
	 * 失败作业的id
     *
     * @var array
     */
    public $failedJobIds;

    /**
     * The batch options.
	 * 批处理选项
     *
     * @var array
     */
    public $options;

    /**
     * The date indicating when the batch was created.
	 * 指示创建批处理的日期
     *
     * @var \Carbon\CarbonImmutable
     */
    public $createdAt;

    /**
     * The date indicating when the batch was cancelled.
	 * 指示批处理被取消的日期
     *
     * @var \Carbon\CarbonImmutable|null
     */
    public $cancelledAt;

    /**
     * The date indicating when the batch was finished.
	 * 指示批处理完成的日期
     *
     * @var \Carbon\CarbonImmutable|null
     */
    public $finishedAt;

    /**
     * Create a new batch instance.
	 * 创建新的批处理实例
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $queue
     * @param  \Illuminate\Bus\BatchRepository  $repository
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
     * @return void
     */
    public function __construct(QueueFactory $queue,
                                BatchRepository $repository,
                                string $id,
                                string $name,
                                int $totalJobs,
                                int $pendingJobs,
                                int $failedJobs,
                                array $failedJobIds,
                                array $options,
                                CarbonImmutable $createdAt,
                                ?CarbonImmutable $cancelledAt = null,
                                ?CarbonImmutable $finishedAt = null)
    {
        $this->queue = $queue;
        $this->repository = $repository;
        $this->id = $id;
        $this->name = $name;
        $this->totalJobs = $totalJobs;
        $this->pendingJobs = $pendingJobs;
        $this->failedJobs = $failedJobs;
        $this->failedJobIds = $failedJobIds;
        $this->options = $options;
        $this->createdAt = $createdAt;
        $this->cancelledAt = $cancelledAt;
        $this->finishedAt = $finishedAt;
    }

    /**
     * Get a fresh instance of the batch represented by this ID.
	 * 获取此ID表示的批处理的新实例
     *
     * @return self
     */
    public function fresh()
    {
        return $this->repository->find($this->id);
    }

    /**
     * Add additional jobs to the batch.
	 * 向批处理添加其他作业
     *
     * @param  \Illuminate\Support\Enumerable|object|array  $jobs
     * @return self
     */
    public function add($jobs)
    {
        $count = 0;

        $jobs = Collection::wrap($jobs)->map(function ($job) use (&$count) {
            $job = $job instanceof Closure ? CallQueuedClosure::create($job) : $job;

            if (is_array($job)) {
                $count += count($job);

                return with($this->prepareBatchedChain($job), function ($chain) {
                    return $chain->first()
                            ->allOnQueue($this->options['queue'] ?? null)
                            ->allOnConnection($this->options['connection'] ?? null)
                            ->chain($chain->slice(1)->values()->all());
                });
            } else {
                $job->withBatchId($this->id);

                $count++;
            }

            return $job;
        });

        $this->repository->transaction(function () use ($jobs, $count) {
            $this->repository->incrementTotalJobs($this->id, $count);

            $this->queue->connection($this->options['connection'] ?? null)->bulk(
                $jobs->all(),
                $data = '',
                $this->options['queue'] ?? null
            );
        });

        return $this->fresh();
    }

    /**
     * Prepare a chain that exists within the jobs being added.
	 * 准备一个存在于正在添加的作业中的链
     *
     * @param  array  $chain
     * @return \Illuminate\Support\Collection
     */
    protected function prepareBatchedChain(array $chain)
    {
        return collect($chain)->map(function ($job) {
            $job = $job instanceof Closure ? CallQueuedClosure::create($job) : $job;

            return $job->withBatchId($this->id);
        });
    }

    /**
     * Get the total number of jobs that have been processed by the batch thus far.
	 * 获取到目前为止该批处理的作业总数
     *
     * @return int
     */
    public function processedJobs()
    {
        return $this->totalJobs - $this->pendingJobs;
    }

    /**
     * Get the percentage of jobs that have been processed (between 0-100).
	 * 获取已处理的作业的百分比（介于0-100之间）
     *
     * @return int
     */
    public function progress()
    {
        return $this->totalJobs > 0 ? round(($this->processedJobs() / $this->totalJobs) * 100) : 0;
    }

    /**
     * Record that a job within the batch finished successfully, executing any callbacks if necessary.
     *
     * @param  string  $jobId
     * @return void
     */
    public function recordSuccessfulJob(string $jobId)
    {
        $counts = $this->decrementPendingJobs($jobId);

        if ($counts->pendingJobs === 0) {
            $this->repository->markAsFinished($this->id);
        }

        if ($counts->pendingJobs === 0 && $this->hasThenCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['then'])->each(function ($handler) use ($batch) {
                $this->invokeHandlerCallback($handler, $batch);
            });
        }

        if ($counts->allJobsHaveRanExactlyOnce() && $this->hasFinallyCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['finally'])->each(function ($handler) use ($batch) {
                $this->invokeHandlerCallback($handler, $batch);
            });
        }
    }

    /**
     * Decrement the pending jobs for the batch.
	 * 减少批处理的待处理作业
     *
     * @param  string  $jobId
     * @return \Illuminate\Bus\UpdatedBatchJobCounts
     */
    public function decrementPendingJobs(string $jobId)
    {
        return $this->repository->decrementPendingJobs($this->id, $jobId);
    }

    /**
     * Determine if the batch has finished executing.
	 * 确定批处理是否已完成执行
     *
     * @return bool
     */
    public function finished()
    {
        return ! is_null($this->finishedAt);
    }

    /**
     * Determine if the batch has "success" callbacks.
	 * 确定批处理是否有"成功"回调
     *
     * @return bool
     */
    public function hasThenCallbacks()
    {
        return isset($this->options['then']) && ! empty($this->options['then']);
    }

    /**
     * Determine if the batch allows jobs to fail without cancelling the batch.
     *
     * @return bool
     */
    public function allowsFailures()
    {
        return Arr::get($this->options, 'allowFailures', false) === true;
    }

    /**
     * Determine if the batch has job failures.
	 * 确定批处理是否有作业失败
     *
     * @return bool
     */
    public function hasFailures()
    {
        return $this->failedJobs > 0;
    }

    /**
     * Record that a job within the batch failed to finish successfully, executing any callbacks if necessary.
     *
     * @param  string  $jobId
     * @param  \Throwable  $e
     * @return void
     */
    public function recordFailedJob(string $jobId, $e)
    {
        $counts = $this->incrementFailedJobs($jobId);

        if ($counts->failedJobs === 1 && ! $this->allowsFailures()) {
            $this->cancel();
        }

        if ($counts->failedJobs === 1 && $this->hasCatchCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['catch'])->each(function ($handler) use ($batch, $e) {
                $this->invokeHandlerCallback($handler, $batch, $e);
            });
        }

        if ($counts->allJobsHaveRanExactlyOnce() && $this->hasFinallyCallbacks()) {
            $batch = $this->fresh();

            collect($this->options['finally'])->each(function ($handler) use ($batch, $e) {
                $this->invokeHandlerCallback($handler, $batch, $e);
            });
        }
    }

    /**
     * Increment the failed jobs for the batch.
	 * 增加批处理的失败作业
     *
     * @param  string  $jobId
     * @return \Illuminate\Bus\UpdatedBatchJobCounts
     */
    public function incrementFailedJobs(string $jobId)
    {
        return $this->repository->incrementFailedJobs($this->id, $jobId);
    }

    /**
     * Determine if the batch has "catch" callbacks.
	 * 确定批处理是否有"catch"回调
     *
     * @return bool
     */
    public function hasCatchCallbacks()
    {
        return isset($this->options['catch']) && ! empty($this->options['catch']);
    }

    /**
     * Determine if the batch has "finally" callbacks.
	 * 确定批处理是否有"finally"回调
     *
     * @return bool
     */
    public function hasFinallyCallbacks()
    {
        return isset($this->options['finally']) && ! empty($this->options['finally']);
    }

    /**
     * Cancel the batch.
	 * 取消批处理
     *
     * @return void
     */
    public function cancel()
    {
        $this->repository->cancel($this->id);
    }

    /**
     * Determine if the batch has been cancelled.
	 * 确定批处理是否已取消
     *
     * @return bool
     */
    public function canceled()
    {
        return $this->cancelled();
    }

    /**
     * Determine if the batch has been cancelled.
	 * 确定批处理是否已取消
     *
     * @return bool
     */
    public function cancelled()
    {
        return ! is_null($this->cancelledAt);
    }

    /**
     * Delete the batch from storage.
	 * 从存储中删除批处理
     *
     * @return void
     */
    public function delete()
    {
        $this->repository->delete($this->id);
    }

    /**
     * Invoke a batch callback handler.
	 * 调用一个批回调处理程序
     *
     * @param  callable  $handler
     * @param  \Illuminate\Bus\Batch  $batch
     * @param  \Throwable|null  $e
     * @return void
     */
    protected function invokeHandlerCallback($handler, Batch $batch, Throwable $e = null)
    {
        try {
            return $handler($batch, $e);
        } catch (Throwable $e) {
            if (function_exists('report')) {
                report($e);
            }
        }
    }

    /**
     * Convert the batch to an array.
	 * 将批处理转换为数组
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'totalJobs' => $this->totalJobs,
            'pendingJobs' => $this->pendingJobs,
            'processedJobs' => $this->processedJobs(),
            'progress' => $this->progress(),
            'failedJobs' => $this->failedJobs,
            'options' => $this->options,
            'createdAt' => $this->createdAt,
            'cancelledAt' => $this->cancelledAt,
            'finishedAt' => $this->finishedAt,
        ];
    }

    /**
     * Get the JSON serializable representation of the object.
	 * 获取对象的JSON可序列化表示
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Dynamically access the batch's "options" via properties.
	 * 通过属性动态访问批处理的"选项"
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->options[$key] ?? null;
    }
}
