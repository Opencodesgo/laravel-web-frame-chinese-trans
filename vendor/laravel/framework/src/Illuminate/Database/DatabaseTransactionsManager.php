<?php
/**
 * Illuminate，数据库，数据库事务管理器
 */

namespace Illuminate\Database;

class DatabaseTransactionsManager
{
    /**
     * All of the recorded transactions.
	 * 所有记录的交易
     *
     * @var \Illuminate\Support\Collection
     */
    protected $transactions;

    /**
     * The database transaction that should be ignored by callbacks.
	 * 应该被回调忽略的数据库事务
     *
     * @var \Illuminate\Database\DatabaseTransactionRecord
     */
    protected $callbacksShouldIgnore;

    /**
     * Create a new database transactions manager instance.
	 * 创建一个新的数据库事务管理器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->transactions = collect();
    }

    /**
     * Start a new database transaction.
	 * 启动一个新的数据库事务
     *
     * @param  string  $connection
     * @param  int  $level
     * @return void
     */
    public function begin($connection, $level)
    {
        $this->transactions->push(
            new DatabaseTransactionRecord($connection, $level)
        );
    }

    /**
     * Rollback the active database transaction.
	 * 回滚活动数据库事务
     *
     * @param  string  $connection
     * @param  int  $level
     * @return void
     */
    public function rollback($connection, $level)
    {
        $this->transactions = $this->transactions->reject(
            fn ($transaction) => $transaction->connection == $connection && $transaction->level > $level
        )->values();

        if ($this->transactions->isEmpty()) {
            $this->callbacksShouldIgnore = null;
        }
    }

    /**
     * Commit the active database transaction.
	 * 提交活动数据库事务
     *
     * @param  string  $connection
     * @return void
     */
    public function commit($connection)
    {
        [$forThisConnection, $forOtherConnections] = $this->transactions->partition(
            fn ($transaction) => $transaction->connection == $connection
        );

        $this->transactions = $forOtherConnections->values();

        $forThisConnection->map->executeCallbacks();

        if ($this->transactions->isEmpty()) {
            $this->callbacksShouldIgnore = null;
        }
    }

    /**
     * Register a transaction callback.
	 * 注册一个事务回调
     *
     * @param  callable  $callback
     * @return void
     */
    public function addCallback($callback)
    {
        if ($current = $this->callbackApplicableTransactions()->last()) {
            return $current->addCallback($callback);
        }

        $callback();
    }

    /**
     * Specify that callbacks should ignore the given transaction when determining if they should be executed.
	 * 指定回调函数在确定是否执行给定事务时应忽略该事务
     *
     * @param  \Illuminate\Database\DatabaseTransactionRecord  $transaction
     * @return $this
     */
    public function callbacksShouldIgnore(DatabaseTransactionRecord $transaction)
    {
        $this->callbacksShouldIgnore = $transaction;

        return $this;
    }

    /**
     * Get the transactions that are applicable to callbacks.
	 * 获取适用于回调的事务
     *
     * @return \Illuminate\Support\Collection
     */
    public function callbackApplicableTransactions()
    {
        return $this->transactions->reject(function ($transaction) {
            return $transaction === $this->callbacksShouldIgnore;
        })->values();
    }

    /**
     * Get all the transactions.
	 * 获取所有的交易
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}
