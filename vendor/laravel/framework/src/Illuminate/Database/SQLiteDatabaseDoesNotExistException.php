<?php
/**
 * Illuminate，数据库，SQLite数据库不存在异常
 */

namespace Illuminate\Database;

use InvalidArgumentException;

class SQLiteDatabaseDoesNotExistException extends InvalidArgumentException
{
    /**
     * The path to the database.
	 * 数据库的路径
     *
     * @var string
     */
    public $path;

    /**
     * Create a new exception instance.
	 * 创建一个新的异常实例
     *
     * @param  string  $path
     * @return void
     */
    public function __construct($path)
    {
        parent::__construct("Database file at path [{$path}] does not exist. Ensure this is an absolute path to the database.");

        $this->path = $path;
    }
}
