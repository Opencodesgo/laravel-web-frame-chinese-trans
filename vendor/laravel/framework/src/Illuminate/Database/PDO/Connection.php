<?php
/**
 * Illuminate，数据库，PDO，连接
 */

namespace Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDO\Result;
use Doctrine\DBAL\Driver\PDO\Statement;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use PDO;
use PDOException;
use PDOStatement;

class Connection implements ServerInfoAwareConnection
{
    /**
     * The underlying PDO connection.
	 * 底层PDO连接
     *
     * @var \PDO
     */
    protected $connection;

    /**
     * Create a new PDO connection instance.
	 * 创建一个新的PDO连接实例
     *
     * @param  \PDO  $connection
     * @return void
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Execute an SQL statement.
	 * 执行SQL语句
     *
     * @param  string  $statement
     * @return int
     */
    public function exec(string $statement): int
    {
        try {
            $result = $this->connection->exec($statement);

            \assert($result !== false);

            return $result;
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Prepare a new SQL statement.
	 * 准备一个新的SQL语句
     *
     * @param  string  $sql
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function prepare(string $sql): StatementInterface
    {
        try {
            return $this->createStatement(
                $this->connection->prepare($sql)
            );
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Execute a new query against the connection.
	 * 对连接执行一个新的查询
     *
     * @param  string  $sql
     * @return \Doctrine\DBAL\Driver\Result
     */
    public function query(string $sql): ResultInterface
    {
        try {
            $stmt = $this->connection->query($sql);

            \assert($stmt instanceof PDOStatement);

            return new Result($stmt);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Get the last insert ID.
	 * 得到最后插入ID
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function lastInsertId($name = null)
    {
        try {
            if ($name === null) {
                return $this->connection->lastInsertId();
            }

            return $this->connection->lastInsertId($name);
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }
    }

    /**
     * Create a new statement instance.
	 * 创建一个新的语句实例
     *
     * @param  \PDOStatement  $stmt
     * @return \Doctrine\DBAL\Driver\PDO\Statement
     */
    protected function createStatement(PDOStatement $stmt): Statement
    {
        return new Statement($stmt);
    }

    /**
     * Begin a new database transaction.
	 * 开始一个新的数据库事务
     *
     * @return void
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a database transaction.
	 * 提交数据库事务
     *
     * @return void
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rollback a database transaction.
	 * 回滚数据库事务
     *
     * @return void
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * Wrap quotes around the given input.
	 * 用引号括住给定的输入
     *
     * @param  string  $input
     * @param  string  $type
     * @return string
     */
    public function quote($input, $type = ParameterType::STRING)
    {
        return $this->connection->quote($input, $type);
    }

    /**
     * Get the server version for the connection.
	 * 获取连接的服务器版本
     *
     * @return string
     */
    public function getServerVersion()
    {
        return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Get the wrapped PDO connection.
	 * 获取封装的PDO连接
     *
     * @return \PDO
     */
    public function getWrappedConnection(): PDO
    {
        return $this->connection;
    }
}
