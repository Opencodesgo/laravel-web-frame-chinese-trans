<?php
/**
 * Illuminate，数据库，查询异常
 */

namespace Illuminate\Database;

use Illuminate\Support\Str;
use PDOException;
use Throwable;

class QueryException extends PDOException
{
    /**
     * The SQL for the query.
	 * 查询SQL
     *
     * @var string
     */
    protected $sql;

    /**
     * The bindings for the query.
	 * 查询的绑定
     *
     * @var array
     */
    protected $bindings;

    /**
     * Create a new query exception instance.
	 * 创建一个新的查询异常实例
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @param  \Throwable  $previous
     * @return void
     */
    public function __construct($sql, array $bindings, Throwable $previous)
    {
        parent::__construct('', 0, $previous);

        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->code = $previous->getCode();
        $this->message = $this->formatMessage($sql, $bindings, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Format the SQL error message.
	 * 格式化SQL错误消息
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @param  \Throwable  $previous
     * @return string
     */
    protected function formatMessage($sql, $bindings, Throwable $previous)
    {
        return $previous->getMessage().' (SQL: '.Str::replaceArray('?', $bindings, $sql).')';
    }

    /**
     * Get the SQL for the query.
	 * 获取查询的SQL
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the bindings for the query.
	 * 获取查询的绑定
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }
}
