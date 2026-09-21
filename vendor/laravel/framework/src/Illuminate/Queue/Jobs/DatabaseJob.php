<?php
/**
 * Illuminate，队列，作业，数据库作业
 */

namespace Illuminate\Queue\Jobs;

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\DatabaseQueue;

class DatabaseJob extends Job implements JobContract
{
    /**
     * The database queue instance.
	 * 数据库队列实例
     *
     * @var \Illuminate\Queue\DatabaseQueue
     */
    protected $database;

    /**
     * The database job payload.
	 * 数据库作业负载
     *
     * @var \stdClass
     */
    protected $job;

    /**
     * Create a new job instance.
	 * 创建一个新的作业实例
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  \Illuminate\Queue\DatabaseQueue  $database
     * @param  \stdClass  $job
     * @param  string  $connectionName
     * @param  string  $queue
     * @return void
     */
    public function __construct(Container $container, DatabaseQueue $database, $job, $connectionName, $queue)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->database = $database;
        $this->container = $container;
        $this->connectionName = $connectionName;
    }

    /**
     * Release the job back into the queue after (n) seconds.
	 * 在(n)秒后将作业释放回队列
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);

        $this->database->deleteAndRelease($this->queue, $this, $delay);
    }

    /**
     * Delete the job from the queue.
	 * 从队列中删除作业
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->database->deleteReserved($this->queue, $this->job->id);
    }

    /**
     * Get the number of times the job has been attempted.
	 * 获取该任务被尝试的次数
     *
     * @return int
     */
    public function attempts()
    {
        return (int) $this->job->attempts;
    }

    /**
     * Get the job identifier.
	 * 获取工作标识符
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->id;
    }

    /**
     * Get the raw body string for the job.
	 * 获取工作的原始主体字符串
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->payload;
    }

    /**
     * Get the database job record.
	 * 获取数据库作业记录
     *
     * @return \Illuminate\Queue\Jobs\DatabaseJobRecord
     */
    public function getJobRecord()
    {
        return $this->job;
    }
}
