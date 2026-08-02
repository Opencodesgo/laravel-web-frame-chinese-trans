<?php
/**
 * Illuminate，队列，事件，作业请求重试
 */

namespace Illuminate\Queue\Events;

class JobRetryRequested
{
    /**
     * The job instance.
	 * 作业实例
     *
     * @var \stdClass
     */
    public $job;

    /**
     * The decoded job payload.
	 * 已解码的作业有效负载
     *
     * @var array|null
     */
    protected $payload = null;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  \stdClass  $job
     * @return void
     */
    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     * The job payload.
	 * 作业有效负载
     *
     * @return array
     */
    public function payload()
    {
        if (is_null($this->payload)) {
            $this->payload = json_decode($this->job->payload, true);
        }

        return $this->payload;
    }
}
