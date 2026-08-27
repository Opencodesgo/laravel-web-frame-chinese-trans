<?php
/**
 * Illuminate，契约，总线，排队调度程序
 */

namespace Illuminate\Contracts\Bus;

interface QueueingDispatcher extends Dispatcher
{
    /**
     * Attempt to find the batch with the given ID.
	 * 尝试查找具有给定ID的批处理
     *
     * @param  string  $batchId
     * @return \Illuminate\Bus\Batch|null
     */
    public function findBatch(string $batchId);

    /**
     * Create a new batch of queueable jobs.
	 * 创建一批新的可排队作业
     *
     * @param  \Illuminate\Support\Collection|array  $jobs
     * @return \Illuminate\Bus\PendingBatch
     */
    public function batch($jobs);

    /**
     * Dispatch a command to its appropriate handler behind a queue.
	 * 将命令分派到队列后面相应的处理程序
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function dispatchToQueue($command);
}
