<?php
/**
 * Illuminate，数据库，模式，Sql Server 构建者
 */

namespace Illuminate\Database\Schema;

class SqlServerBuilder extends Builder
{
    /**
     * Create a database in the schema.
	 * 在模式中创建数据库
     *
     * @param  string  $name
     * @return bool
     */
    public function createDatabase($name)
    {
        return $this->connection->statement(
            $this->grammar->compileCreateDatabase($name, $this->connection)
        );
    }

    /**
     * Drop a database from the schema if the database exists.
	 * 如果数据库存在，则从模式中删除该数据库。
     *
     * @param  string  $name
     * @return bool
     */
    public function dropDatabaseIfExists($name)
    {
        return $this->connection->statement(
            $this->grammar->compileDropDatabaseIfExists($name)
        );
    }

    /**
     * Drop all tables from the database.
	 * 从数据库中删除所有表
     *
     * @return void
     */
    public function dropAllTables()
    {
        $this->connection->statement($this->grammar->compileDropAllForeignKeys());

        $this->connection->statement($this->grammar->compileDropAllTables());
    }

    /**
     * Drop all views from the database.
	 * 从数据库中删除所有视图
     *
     * @return void
     */
    public function dropAllViews()
    {
        $this->connection->statement($this->grammar->compileDropAllViews());
    }

    /**
     * Drop all tables from the database.
	 * 从数据库中删除所有表
     *
     * @return array
     */
    public function getAllTables()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables()
        );
    }

    /**
     * Get all of the view names for the database.
	 * 获取数据库的所有视图名称
     *
     * @return array
     */
    public function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews()
        );
    }
}
