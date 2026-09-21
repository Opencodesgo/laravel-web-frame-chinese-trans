<?php
/**
 * Illuminate，数据库，事件，模式倾倒
 */

namespace Illuminate\Database\Events;

class SchemaDumped
{
    /**
     * The database connection instance.
	 * 数据库连接实例
     *
     * @var \Illuminate\Database\Connection
     */
    public $connection;

    /**
     * The database connection name.
	 * 数据库连接名称
     *
     * @var string
     */
    public $connectionName;

    /**
     * The path to the schema dump.
	 * 模式转储的路径
     *
     * @var string
     */
    public $path;

    /**
     * Create a new event instance.
	 * 创建事件实例
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  string  $path
     * @return void
     */
    public function __construct($connection, $path)
    {
        $this->connection = $connection;
        $this->connectionName = $connection->getName();
        $this->path = $path;
    }
}
