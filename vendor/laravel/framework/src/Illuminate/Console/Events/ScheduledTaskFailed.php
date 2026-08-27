<?php
/**
 * Illuminate，控制台，事件，计划任务失败
 */

namespace Illuminate\Console\Events;

use Illuminate\Console\Scheduling\Event;
use Throwable;

class ScheduledTaskFailed
{
    /**
     * The scheduled event that failed.
	 * 预定事件失败
     *
     * @var \Illuminate\Console\Scheduling\Event
     */
    public $task;

    /**
     * The exception that was thrown.
	 * 被抛出的异常
     *
     * @var \Throwable
     */
    public $exception;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  \Illuminate\Console\Scheduling\Event  $task
     * @param  \Throwable  $exception
     * @return void
     */
    public function __construct(Event $task, Throwable $exception)
    {
        $this->task = $task;
        $this->exception = $exception;
    }
}
