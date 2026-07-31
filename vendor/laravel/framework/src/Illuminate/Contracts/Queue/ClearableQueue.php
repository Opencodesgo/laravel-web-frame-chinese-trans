<?php
/**
 * Illuminate，契约，队列，可清除队列
 */

namespace Illuminate\Contracts\Queue;

interface ClearableQueue
{
    /**
     * Delete all of the jobs from the queue.
	 * 从队列中删除所有任务
     *
     * @param  string  $queue
     * @return int
     */
    public function clear($queue);
}
