<?php
/**
 * Illuminate，支持，测试，佯装，待定批 Fake
 */

namespace Illuminate\Support\Testing\Fakes;

use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Collection;

class PendingBatchFake extends PendingBatch
{
    /**
     * The fake bus instance.
	 * 假总线实例
     *
     * @var \Illuminate\Support\Testing\Fakes\BusFake
     */
    protected $bus;

    /**
     * Create a new pending batch instance.
	 * 创建一个新的挂起批处理实例
     *
     * @param  \Illuminate\Support\Testing\Fakes\BusFake  $bus
     * @param  \Illuminate\Support\Collection  $jobs
     * @return void
     */
    public function __construct(BusFake $bus, Collection $jobs)
    {
        $this->bus = $bus;
        $this->jobs = $jobs;
    }

    /**
     * Dispatch the batch.
	 * 调度批次
     *
     * @return \Illuminate\Bus\Batch
     */
    public function dispatch()
    {
        return $this->bus->recordPendingBatch($this);
    }

    /**
     * Dispatch the batch after the response is sent to the browser.
	 * 在将响应发送到浏览器后，分派该批处理。
     *
     * @return \Illuminate\Bus\Batch
     */
    public function dispatchAfterResponse()
    {
        return $this->bus->recordPendingBatch($this);
    }
}
