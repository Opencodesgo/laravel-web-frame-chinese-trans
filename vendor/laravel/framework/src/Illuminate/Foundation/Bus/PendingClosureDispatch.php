<?php
/**
 * Illuminate，基础，总线，等待关闭调度
 */

namespace Illuminate\Foundation\Bus;

use Closure;

class PendingClosureDispatch extends PendingDispatch
{
    /**
     * Add a callback to be executed if the job fails.
	 * 添加一个回调，在作业失败时执行。
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function catch(Closure $callback)
    {
        $this->job->onFailure($callback);

        return $this;
    }
}
