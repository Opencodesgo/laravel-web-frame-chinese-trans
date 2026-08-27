<?php
/**
 * Illuminate，数据库，事件，数据库繁忙
 */

namespace Illuminate\Database\Events;

class DatabaseBusy
{
    /**
     * The database connection name.
	 * 数据库连接名称
     *
     * @var string
     */
    public $connectionName;

    /**
     * The number of open connections.
	 * 打开的连接数
     *
     * @var int
     */
    public $connections;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  string  $connectionName
     * @param  int  $connections
     */
    public function __construct($connectionName, $connections)
    {
        $this->connectionName = $connectionName;
        $this->connections = $connections;
    }
}
