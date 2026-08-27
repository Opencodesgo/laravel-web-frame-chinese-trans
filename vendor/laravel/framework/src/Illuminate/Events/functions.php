<?php
/**
 * Illuminate，事件，函数
 */

namespace Illuminate\Events;

use Closure;

if (! function_exists('Illuminate\Events\queueable')) {
    /**
     * Create a new queued Closure event listener.
	 * 创建一个新的队列Closure事件侦听器
     *
     * @param  \Closure  $closure
     * @return \Illuminate\Events\QueuedClosure
     */
    function queueable(Closure $closure)
    {
        return new QueuedClosure($closure);
    }
}
