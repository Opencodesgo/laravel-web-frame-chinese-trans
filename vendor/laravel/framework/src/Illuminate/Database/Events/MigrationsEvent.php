<?php
/**
 * Illuminate，数据库，事件，迁移事件
 */

namespace Illuminate\Database\Events;

use Illuminate\Contracts\Database\Events\MigrationEvent as MigrationEventContract;

abstract class MigrationsEvent implements MigrationEventContract
{
    /**
     * The migration method that was invoked.
	 * 被调用的迁移方法
     *
     * @var string
     */
    public $method;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  string  $method
     * @return void
     */
    public function __construct($method)
    {
        $this->method = $method;
    }
}
