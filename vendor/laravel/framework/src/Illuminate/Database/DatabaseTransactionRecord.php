<?php
/**
 * Illuminate，数据库，数据库事务记录
 */

namespace Illuminate\Database;

class DatabaseTransactionRecord
{
    /**
     * The name of the database connection.
	 * 数据库连接的名称
     *
     * @var string
     */
    public $connection;

    /**
     * The transaction level.
	 * 事务级别
     *
     * @var int
     */
    public $level;

    /**
     * The callbacks that should be executed after committing.
	 * 提交后应该执行的回调函数
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * Create a new database transaction record instance.
	 * 创建一个新的数据库事务记录实例
     *
     * @param  string  $connection
     * @param  int  $level
     * @return void
     */
    public function __construct($connection, $level)
    {
        $this->connection = $connection;
        $this->level = $level;
    }

    /**
     * Register a callback to be executed after committing.
	 * 注册一个在提交后执行的回调函数
     *
     * @param  callable  $callback
     * @return void
     */
    public function addCallback($callback)
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Execute all of the callbacks.
	 * 执行所有回调
     *
     * @return void
     */
    public function executeCallbacks()
    {
        foreach ($this->callbacks as $callback) {
            call_user_func($callback);
        }
    }

    /**
     * Get all of the callbacks.
	 * 获得所有回调
     *
     * @return array
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }
}
