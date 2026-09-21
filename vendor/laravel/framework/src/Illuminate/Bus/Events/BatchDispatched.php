<?php
/**
 * Illuminate，总线，事件，批量发送
 */

namespace Illuminate\Bus\Events;

use Illuminate\Bus\Batch;

class BatchDispatched
{
    /**
     * The batch instance.
	 * 批实例
     *
     * @var \Illuminate\Bus\Batch
     */
    public $batch;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  \Illuminate\Bus\Batch  $batch
     * @return void
     */
    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }
}
