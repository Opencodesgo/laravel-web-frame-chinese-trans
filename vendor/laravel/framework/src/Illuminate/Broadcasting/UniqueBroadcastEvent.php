<?php
/**
 * Illuminate，广播，唯一广播事件
 */

namespace Illuminate\Broadcasting;

use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UniqueBroadcastEvent extends BroadcastEvent implements ShouldBeUnique
{
    /**
     * The unique lock identifier.
	 * 唯一锁定标识符
     *
     * @var mixed
     */
    public $uniqueId;

    /**
     * The number of seconds the unique lock should be maintained.
	 * 应该维护唯一锁的秒数
     *
     * @var int
     */
    public $uniqueFor;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  mixed  $event
     * @return void
     */
    public function __construct($event)
    {
        $this->uniqueId = get_class($event);

        if (method_exists($event, 'uniqueId')) {
            $this->uniqueId .= $event->uniqueId();
        } elseif (property_exists($event, 'uniqueId')) {
            $this->uniqueId .= $event->uniqueId;
        }

        if (method_exists($event, 'uniqueFor')) {
            $this->uniqueFor = $event->uniqueFor();
        } elseif (property_exists($event, 'uniqueFor')) {
            $this->uniqueFor = $event->uniqueFor;
        }

        parent::__construct($event);
    }

    /**
     * Resolve the cache implementation that should manage the event's uniqueness.
	 * 解析应该管理事件唯一性的缓存实现
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function uniqueVia()
    {
        return method_exists($this->event, 'uniqueVia')
                ? $this->event->uniqueVia()
                : Container::getInstance()->make(Repository::class);
    }
}
