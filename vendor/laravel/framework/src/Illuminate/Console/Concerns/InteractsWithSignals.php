<?php
/**
 * Illuminate，控制台，问题，与信号交互
 */

namespace Illuminate\Console\Concerns;

use Illuminate\Console\Signals;
use Illuminate\Support\Arr;

trait InteractsWithSignals
{
    /**
     * The signal registrar instance.
	 * 信号注册器实例
     *
     * @var \Illuminate\Console\Signals|null
     */
    protected $signals;

    /**
     * Define a callback to be run when the given signal(s) occurs.
	 * 定义一个回调函数，在给定信号出现时运行。
     *
     * @param  iterable<array-key, int>|int  $signals
     * @param  callable(int $signal): void  $callback
     * @return void
     */
    public function trap($signals, $callback)
    {
        Signals::whenAvailable(function () use ($signals, $callback) {
            $this->signals ??= new Signals(
                $this->getApplication()->getSignalRegistry(),
            );

            collect(Arr::wrap($signals))
                ->each(fn ($signal) => $this->signals->register($signal, $callback));
        });
    }

    /**
     * Untrap signal handlers set within the command's handler.
	 * 解开命令处理程序中设置的信号处理程序
     *
     * @return void
     *
     * @internal
     */
    public function untrap()
    {
        if (! is_null($this->signals)) {
            $this->signals->unregister();

            $this->signals = null;
        }
    }
}
