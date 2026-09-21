<?php
/**
 * Illuminate，队列，事件，队列繁忙
 */

namespace Illuminate\Queue\Events;

class QueueBusy
{
    /**
     * The connection name.
	 * 连接名称
     *
     * @var string
     */
    public $connection;

    /**
     * The queue name.
	 * 队列名称
     *
     * @var string
     */
    public $queue;

    /**
     * The size of the queue.
	 * 队列大小
     *
     * @var int
     */
    public $size;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  string  $connection
     * @param  string  $queue
     * @param  int  $size
     * @return void
     */
    public function __construct($connection, $queue, $size)
    {
        $this->connection = $connection;
        $this->queue = $queue;
        $this->size = $size;
    }
}
