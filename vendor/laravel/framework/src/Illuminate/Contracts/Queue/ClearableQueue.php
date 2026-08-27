<?php
/**
 * Illuminate，契约，队列，可清除队列
 */

namespace Illuminate\Contracts\Queue;

interface ClearableQueue
{
    /**
     * Delete all of the jobs from the queue.
	 * 删除所有任务从队列中
     *
     * @param  string  $queue
     * @return int
     */
    public function clear($queue);
}
