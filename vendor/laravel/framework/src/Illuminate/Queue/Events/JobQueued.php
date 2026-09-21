<?php
/**
 * Illuminate，队列，事件，作业排队
 */

namespace Illuminate\Queue\Events;

class JobQueued
{
    /**
     * The connection name.
	 * 连接名称
     *
     * @var string
     */
    public $connectionName;

    /**
     * The job ID.
	 * 作业ID
     *
     * @var string|int|null
     */
    public $id;

    /**
     * The job instance.
	 * 作业实例
     *
     * @var \Closure|string|object
     */
    public $job;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  string  $connectionName
     * @param  string|int|null  $id
     * @param  \Closure|string|object  $job
     * @return void
     */
    public function __construct($connectionName, $id, $job)
    {
        $this->connectionName = $connectionName;
        $this->id = $id;
        $this->job = $job;
    }
}
