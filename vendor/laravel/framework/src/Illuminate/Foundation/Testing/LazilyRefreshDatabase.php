<?php
/**
 * Illuminate，基础，测试，延迟刷新数据库
 */

namespace Illuminate\Foundation\Testing;

trait LazilyRefreshDatabase
{
    use RefreshDatabase {
        refreshDatabase as baseRefreshDatabase;
    }

    /**
     * Define hooks to migrate the database before and after each test.
	 * 定义钩子，以便在每次测试之前和之后迁移数据库。
     *
     * @return void
     */
    public function refreshDatabase()
    {
        $database = $this->app->make('db');

        $database->beforeExecuting(function () {
            if (RefreshDatabaseState::$lazilyRefreshed) {
                return;
            }

            RefreshDatabaseState::$lazilyRefreshed = true;

            $this->baseRefreshDatabase();
        });

        $this->beforeApplicationDestroyed(function () {
            RefreshDatabaseState::$lazilyRefreshed = false;
        });
    }
}
