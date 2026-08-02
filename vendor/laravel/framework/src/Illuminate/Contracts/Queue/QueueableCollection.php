<?php
/**
 * Illuminate，契约，队列，可队列集合
 */

namespace Illuminate\Contracts\Queue;

interface QueueableCollection
{
    /**
     * Get the type of the entities being queued.
	 * 获取正在排队的实体的类型
     *
     * @return string|null
     */
    public function getQueueableClass();

    /**
     * Get the identifiers for all of the entities.
	 * 获取所有实体的标识符
     *
     * @return array
     */
    public function getQueueableIds();

    /**
     * Get the relationships of the entities being queued.
	 * 获取正在排队的实体之间的关系
     *
     * @return array
     */
    public function getQueueableRelations();

    /**
     * Get the connection of the entities being queued.
	 * 获取正在排队的实体的连接
     *
     * @return string|null
     */
    public function getQueueableConnection();
}
