<?php
/**
 * Illuminate，队列，Beanstalkd 队列
 */

namespace Illuminate\Queue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Jobs\BeanstalkdJob;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;

class BeanstalkdQueue extends Queue implements QueueContract
{
    /**
     * The Pheanstalk instance.
	 * Pheanstalk实例
     *
     * @var \Pheanstalk\Pheanstalk
     */
    protected $pheanstalk;

    /**
     * The name of the default tube.
	 * 默认管道的名称
     *
     * @var string
     */
    protected $default;

    /**
     * The "time to run" for all pushed jobs.
	 * 所有被推的工作的"时间到了"
     *
     * @var int
     */
    protected $timeToRun;

    /**
     * The maximum number of seconds to block for a job.
	 * 阻塞作业的最大秒数
     *
     * @var int
     */
    protected $blockFor;

    /**
     * Create a new Beanstalkd queue instance.
	 * 创建一个新的beanstald队列实例
     *
     * @param  \Pheanstalk\Pheanstalk  $pheanstalk
     * @param  string  $default
     * @param  int  $timeToRun
     * @param  int  $blockFor
     * @param  bool  $dispatchAfterCommit
     * @return void
     */
    public function __construct(Pheanstalk $pheanstalk,
                                $default,
                                $timeToRun,
                                $blockFor = 0,
                                $dispatchAfterCommit = false)
    {
        $this->default = $default;
        $this->blockFor = $blockFor;
        $this->timeToRun = $timeToRun;
        $this->pheanstalk = $pheanstalk;
        $this->dispatchAfterCommit = $dispatchAfterCommit;
    }

    /**
     * Get the size of the queue.
	 * 得到队列大小
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);

        return (int) $this->pheanstalk->statsTube($queue)->current_jobs_ready;
    }

    /**
     * Push a new job onto the queue.
	 * 将新作业推送到队列中
     *
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $this->getQueue($queue), $data),
            $queue,
            null,
            function ($payload, $queue) {
                return $this->pushRaw($payload, $queue);
            }
        );
    }

    /**
     * Push a raw payload onto the queue.
	 * 将原始有效负载推入队列
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->pheanstalk->useTube($this->getQueue($queue))->put(
            $payload, Pheanstalk::DEFAULT_PRIORITY, Pheanstalk::DEFAULT_DELAY, $this->timeToRun
        );
    }

    /**
     * Push a new job onto the queue after (n) seconds.
	 * 在(n)秒后将一个新作业推送到队列中
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $this->getQueue($queue), $data),
            $queue,
            $delay,
            function ($payload, $queue, $delay) {
                return $this->pheanstalk->useTube($this->getQueue($queue))->put(
                    $payload,
                    Pheanstalk::DEFAULT_PRIORITY,
                    $this->secondsUntil($delay),
                    $this->timeToRun
                );
            }
        );
    }

    /**
     * Push an array of jobs onto the queue.
	 * 将一组作业推入队列
     *
     * @param  array  $jobs
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return void
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            if (isset($job->delay)) {
                $this->later($job->delay, $job, $data, $queue);
            } else {
                $this->push($job, $data, $queue);
            }
        }
    }

    /**
     * Pop the next job off of the queue.
	 * 将下一个作业从队列中弹出
     *
     * @param  string|null  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $job = $this->pheanstalk->watchOnly($queue)->reserveWithTimeout($this->blockFor);

        if ($job instanceof PheanstalkJob) {
            return new BeanstalkdJob(
                $this->container, $this->pheanstalk, $job, $this->connectionName, $queue
            );
        }
    }

    /**
     * Delete a message from the Beanstalk queue.
	 * 从Beanstalk队列中删除消息
     *
     * @param  string  $queue
     * @param  string|int  $id
     * @return void
     */
    public function deleteMessage($queue, $id)
    {
        $queue = $this->getQueue($queue);

        $this->pheanstalk->useTube($queue)->delete(new PheanstalkJob($id, ''));
    }

    /**
     * Get the queue or return the default.
	 * 获取队列或返回默认值
     *
     * @param  string|null  $queue
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the underlying Pheanstalk instance.
	 * 获取底层Pheanstalk实例
     *
     * @return \Pheanstalk\Pheanstalk
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }
}
