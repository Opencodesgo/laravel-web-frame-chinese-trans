<?php
/**
 * Illuminate，队列，事件，工作程序停止中
 */

namespace Illuminate\Queue\Events;

class WorkerStopping
{
    /**
     * The worker exit status.
	 * 工作者退出状态
     *
     * @var int
     */
    public $status;

    /**
     * The worker options.
	 * 工作者选项
     *
     * @var \Illuminate\Queue\WorkerOptions|null
     */
    public $workerOptions;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  int  $status
     * @param  \Illuminate\Queue\WorkerOptions|null  $workerOptions
     * @return void
     */
    public function __construct($status = 0, $workerOptions = null)
    {
        $this->status = $status;
        $this->workerOptions = $workerOptions;
    }
}
