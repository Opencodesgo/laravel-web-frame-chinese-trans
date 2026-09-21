<?php
/**
 * Illuminate，队列，失败，查找失败的作业提供程序
 */

namespace Illuminate\Queue\Failed;

use DateTimeInterface;

interface PrunableFailedJobProvider
{
    /**
     * Prune all of the entries older than the given date.
	 * 删除所有比给定日期早的条目
     *
     * @param  \DateTimeInterface  $before
     * @return int
     */
    public function prune(DateTimeInterface $before);
}
