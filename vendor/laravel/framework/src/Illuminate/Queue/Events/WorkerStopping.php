<?php
/**
 * Illuminate，队列，事件，工作进程停止
 */

namespace Illuminate\Queue\Events;

class WorkerStopping
{
    /**
     * The exit status.
	 * 退出状态
     *
     * @var int
     */
    public $status;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  int  $status
     * @return void
     */
    public function __construct($status = 0)
    {
        $this->status = $status;
    }
}
