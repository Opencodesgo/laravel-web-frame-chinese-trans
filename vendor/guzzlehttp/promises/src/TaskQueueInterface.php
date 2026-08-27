<?php
/**
 * GuzzleHttp，Promise，任务队列接口
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

interface TaskQueueInterface
{
    /**
     * Returns true if the queue is empty.
	 * 如果队列为空则返回true
     */
    public function isEmpty(): bool;

    /**
     * Adds a task to the queue that will be executed the next time run is
     * called.
	 * 向队列中添加将在下次运行时执行的任务
     */
    public function add(callable $task): void;

    /**
     * Execute all of the pending task in the queue.
	 * 执行队列中所有挂起的任务
     */
    public function run(): void;
}
