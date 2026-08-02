<?php
/**
 * Illuminate，契约，总线，排队调度程序
 */

namespace Illuminate\Contracts\Bus;

interface QueueingDispatcher extends Dispatcher
{
    /**
     * Dispatch a command to its appropriate handler behind a queue.
	 * 将命令分派到队列后面相应的处理程序
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function dispatchToQueue($command);
}
