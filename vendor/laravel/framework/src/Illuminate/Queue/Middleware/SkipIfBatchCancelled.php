<?php
/**
 * Illuminate，队列，中间件，批量取消跳过
 */

namespace Illuminate\Queue\Middleware;

class SkipIfBatchCancelled
{
    /**
     * Process the job.
	 * 作业进程 
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        if (method_exists($job, 'batch') && $job->batch()?->cancelled()) {
            return;
        }

        $next($job);
    }
}
