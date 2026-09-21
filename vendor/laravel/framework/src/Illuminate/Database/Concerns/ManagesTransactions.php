<?php
/**
 * Illuminate，数据库，问题，管理事务
 */

namespace Illuminate\Database\Concerns;

use Closure;
use Illuminate\Database\DeadlockException;
use RuntimeException;
use Throwable;

trait ManagesTransactions
{
    /**
     * Execute a Closure within a transaction.
	 * 执行事务中闭包
     *
     * @param  \Closure  $callback
     * @param  int  $attempts
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            // We'll simply execute the given callback within a try / catch block and if we
            // catch any exception we can rollback this transaction so that none of this
            // gets actually persisted to a database or stored in a permanent fashion.
			// 我们将简单地在try / catch块中执行给定的回调。
            try {
                $callbackResult = $callback($this);
            }

            // If we catch an exception we'll rollback this transaction and try again if we
            // are not out of attempts. If we are out of attempts we will just throw the
            // exception back out, and let the developer handle an uncaught exception.
			// 如果我们捕获一个异常，我们将回滚这个事务并且尝试不是没有尝试过。
            catch (Throwable $e) {
                $this->handleTransactionException(
                    $e, $currentAttempt, $attempts
                );

                continue;
            }

            try {
                if ($this->transactions == 1) {
                    $this->fireConnectionEvent('committing');
                    $this->getPdo()->commit();
                }

                $this->transactions = max(0, $this->transactions - 1);

                if ($this->afterCommitCallbacksShouldBeExecuted()) {
                    $this->transactionsManager?->commit($this->getName());
                }
            } catch (Throwable $e) {
                $this->handleCommitTransactionException(
                    $e, $currentAttempt, $attempts
                );

                continue;
            }

            $this->fireConnectionEvent('committed');

            return $callbackResult;
        }
    }

    /**
     * Handle an exception encountered when running a transacted statement.
	 * 处理运行事务语句时遇到的异常
     *
     * @param  \Throwable  $e
     * @param  int  $currentAttempt
     * @param  int  $maxAttempts
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleTransactionException(Throwable $e, $currentAttempt, $maxAttempts)
    {
        // On a deadlock, MySQL rolls back the entire transaction so we can't just
        // retry the query. We have to throw this exception all the way out and
        // let the developer handle it in another way. We will decrement too.
		// 当发生死锁时，MySQL回滚整个事务以便我们不能重试查询。
        if ($this->causedByConcurrencyError($e) &&
            $this->transactions > 1) {
            $this->transactions--;

            $this->transactionsManager?->rollback(
                $this->getName(), $this->transactions
            );

            throw new DeadlockException($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 0, $e);
        }

        // If there was an exception we will rollback this transaction and then we
        // can check if we have exceeded the maximum attempt count for this and
        // if we haven't we will return and try this query again in our loop.
		// 如果出现异常，我们将回滚此事务。
        $this->rollBack();

        if ($this->causedByConcurrencyError($e) &&
            $currentAttempt < $maxAttempts) {
            return;
        }

        throw $e;
    }

    /**
     * Start a new database transaction.
	 * 启动一个新的数据库事务
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function beginTransaction()
    {
        $this->createTransaction();

        $this->transactions++;

        $this->transactionsManager?->begin(
            $this->getName(), $this->transactions
        );

        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Create a transaction within the database.
	 * 在数据库中创建一个事务
     *
     * @return void
     *
     * @throws \Throwable
     */
    protected function createTransaction()
    {
        if ($this->transactions == 0) {
            $this->reconnectIfMissingConnection();

            try {
                $this->getPdo()->beginTransaction();
            } catch (Throwable $e) {
                $this->handleBeginTransactionException($e);
            }
        } elseif ($this->transactions >= 1 && $this->queryGrammar->supportsSavepoints()) {
            $this->createSavepoint();
        }
    }

    /**
     * Create a save point within the database.
	 * 在数据库中创建一个保存点
     *
     * @return void
     *
     * @throws \Throwable
     */
    protected function createSavepoint()
    {
        $this->getPdo()->exec(
            $this->queryGrammar->compileSavepoint('trans'.($this->transactions + 1))
        );
    }

    /**
     * Handle an exception from a transaction beginning.
	 * 从事务开始处理异常
     *
     * @param  \Throwable  $e
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleBeginTransactionException(Throwable $e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();

            $this->getPdo()->beginTransaction();
        } else {
            throw $e;
        }
    }

    /**
     * Commit the active database transaction.
	 * 提交活动数据库事务
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function commit()
    {
        if ($this->transactionLevel() == 1) {
            $this->fireConnectionEvent('committing');
            $this->getPdo()->commit();
        }

        $this->transactions = max(0, $this->transactions - 1);

        if ($this->afterCommitCallbacksShouldBeExecuted()) {
            $this->transactionsManager?->commit($this->getName());
        }

        $this->fireConnectionEvent('committed');
    }

    /**
     * Determine if after commit callbacks should be executed.
	 * 确定提交后是否应该执行回调
     *
     * @return bool
     */
    protected function afterCommitCallbacksShouldBeExecuted()
    {
        return $this->transactions == 0 ||
            ($this->transactionsManager &&
             $this->transactionsManager->callbackApplicableTransactions()->count() === 1);
    }

    /**
     * Handle an exception encountered when committing a transaction.
	 * 处理提交事务时遇到的异常
     *
     * @param  \Throwable  $e
     * @param  int  $currentAttempt
     * @param  int  $maxAttempts
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleCommitTransactionException(Throwable $e, $currentAttempt, $maxAttempts)
    {
        $this->transactions = max(0, $this->transactions - 1);

        if ($this->causedByConcurrencyError($e) && $currentAttempt < $maxAttempts) {
            return;
        }

        if ($this->causedByLostConnection($e)) {
            $this->transactions = 0;
        }

        throw $e;
    }

    /**
     * Rollback the active database transaction.
	 * 回滚活动数据库事务
     *
     * @param  int|null  $toLevel
     * @return void
     *
     * @throws \Throwable
     */
    public function rollBack($toLevel = null)
    {
        // We allow developers to rollback to a certain transaction level. We will verify
        // that this given transaction level is valid before attempting to rollback to
        // that level. If it's not we will just return out and not attempt anything.
		// 我们允许开发人员回滚到某个事务级别。
        $toLevel = is_null($toLevel)
                    ? $this->transactions - 1
                    : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        // Next, we will actually perform this rollback within this database and fire the
        // rollback event. We will also set the current transaction level to the given
        // level that was passed into this method so it will be right from here out.
		// 接下来，我们将在这个数据库中实际执行这个回滚，并触发回滚事件。
        try {
            $this->performRollBack($toLevel);
        } catch (Throwable $e) {
            $this->handleRollBackException($e);
        }

        $this->transactions = $toLevel;

        $this->transactionsManager?->rollback(
            $this->getName(), $this->transactions
        );

        $this->fireConnectionEvent('rollingBack');
    }

    /**
     * Perform a rollback within the database.
	 * 在数据库中执行回滚
     *
     * @param  int  $toLevel
     * @return void
     *
     * @throws \Throwable
     */
    protected function performRollBack($toLevel)
    {
        if ($toLevel == 0) {
            $pdo = $this->getPdo();

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->getPdo()->exec(
                $this->queryGrammar->compileSavepointRollBack('trans'.($toLevel + 1))
            );
        }
    }

    /**
     * Handle an exception from a rollback.
	 * 处理回滚的异常
     *
     * @param  \Throwable  $e
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleRollBackException(Throwable $e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->transactions = 0;

            $this->transactionsManager?->rollback(
                $this->getName(), $this->transactions
            );
        }

        throw $e;
    }

    /**
     * Get the number of active transactions.
	 * 获取活动事务的数量
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }

    /**
     * Execute the callback after a transaction commits.
	 * 在事务提交后执行回调
     *
     * @param  callable  $callback
     * @return void
     *
     * @throws \RuntimeException
     */
    public function afterCommit($callback)
    {
        if ($this->transactionsManager) {
            return $this->transactionsManager->addCallback($callback);
        }

        throw new RuntimeException('Transactions Manager has not been set.');
    }
}
