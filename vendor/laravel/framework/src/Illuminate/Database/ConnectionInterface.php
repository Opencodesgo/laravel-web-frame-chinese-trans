<?php
/**
 * Illuminate，数据库，连接接口
 */

namespace Illuminate\Database;

use Closure;

interface ConnectionInterface
{
    /**
     * Begin a fluent query against a database table.
	 * 开始对数据库表进行流畅的查询
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|string  $table
     * @param  string|null  $as
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table, $as = null);

    /**
     * Get a new raw query expression.
	 * 获取一个新的原始查询表达式
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value);

    /**
     * Run a select statement and return a single result.
	 * 运行一个select语句并返回一个结果
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true);

    /**
     * Run a select statement against the database.
	 * 对数据库运行一条选择语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true);

    /**
     * Run a select statement against the database and returns a generator.
	 * 对数据库运行select语句并返回生成器
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true);

    /**
     * Run an insert statement against the database.
	 * 对数据库运行一条插入语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function insert($query, $bindings = []);

    /**
     * Run an update statement against the database.
	 * 对数据库运行一条更新语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return int
     */
    public function update($query, $bindings = []);

    /**
     * Run a delete statement against the database.
	 * 对数据库运行delete语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return int
     */
    public function delete($query, $bindings = []);

    /**
     * Execute an SQL statement and return the boolean result.
	 * 执行SQL语句并返回布尔结果
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function statement($query, $bindings = []);

    /**
     * Run an SQL statement and get the number of rows affected.
	 * 运行一条SQL语句，获取受影响的行数
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = []);

    /**
     * Run a raw, unprepared query against the PDO connection.
	 * 对PDO连接运行一个未准备的原始查询
     *
     * @param  string  $query
     * @return bool
     */
    public function unprepared($query);

    /**
     * Prepare the query bindings for execution.
	 * 准备执行查询绑定
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings);

    /**
     * Execute a Closure within a transaction.
	 * 在事务中执行闭包
     *
     * @param  \Closure  $callback
     * @param  int  $attempts
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1);

    /**
     * Start a new database transaction.
	 * 启动一个新的数据库事务
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
	 * 提交活动数据库事务
     *
     * @return void
     */
    public function commit();

    /**
     * Rollback the active database transaction.
	 * 回滚活动数据库事务
     *
     * @return void
     */
    public function rollBack();

    /**
     * Get the number of active transactions.
	 * 获取活动事务的数量
     *
     * @return int
     */
    public function transactionLevel();

    /**
     * Execute the given callback in "dry run" mode.
	 * 以"预演"模式执行给定的回调函数
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(Closure $callback);

    /**
     * Get the name of the connected database.
	 * 获取所连接数据库的名称
     *
     * @return string
     */
    public function getDatabaseName();
}
