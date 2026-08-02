<?php
/**
 * Illuminate，数据库，迁移，迁移抽象类
 */

namespace Illuminate\Database\Migrations;

abstract class Migration
{
    /**
     * The name of the database connection to use.
	 * 要使用的数据库连接的名称
     *
     * @var string|null
     */
    protected $connection;

    /**
     * Enables, if supported, wrapping the migration within a transaction.
	 * 启用（如果支持）在事务中包装迁移
     *
     * @var bool
     */
    public $withinTransaction = true;

    /**
     * Get the migration connection name.
	 * 获取迁移连接名称
     *
     * @return string|null
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
