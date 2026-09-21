<?php
/**
 * Illuminate，总线，可批量资源库
 */

namespace Illuminate\Bus;

use DateTimeInterface;

interface PrunableBatchRepository extends BatchRepository
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
