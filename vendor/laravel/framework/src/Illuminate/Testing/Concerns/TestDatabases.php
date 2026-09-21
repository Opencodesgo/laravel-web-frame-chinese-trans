<?php
/**
 * Illuminate, 测试, 问题，测试数据库
 */

namespace Illuminate\Testing\Concerns;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Schema;

trait TestDatabases
{
    /**
     * Indicates if the test database schema is up to date.
	 * 指示测试数据库架构是否是最新的
     *
     * @var bool
     */
    protected static $schemaIsUpToDate = false;

    /**
     * Boot a test database.
	 * 启动一个测试数据库
     *
     * @return void
     */
    protected function bootTestDatabase()
    {
        ParallelTesting::setUpProcess(function () {
            $this->whenNotUsingInMemoryDatabase(function ($database) {
                if (ParallelTesting::option('recreate_databases')) {
                    Schema::dropDatabaseIfExists(
                        $this->testDatabase($database)
                    );
                }
            });
        });

        ParallelTesting::setUpTestCase(function ($testCase) {
            $uses = array_flip(class_uses_recursive(get_class($testCase)));

            $databaseTraits = [
                Testing\DatabaseMigrations::class,
                Testing\DatabaseTransactions::class,
                Testing\DatabaseTruncation::class,
                Testing\RefreshDatabase::class,
            ];

            if (Arr::hasAny($uses, $databaseTraits) && ! ParallelTesting::option('without_databases')) {
                $this->whenNotUsingInMemoryDatabase(function ($database) use ($uses) {
                    [$testDatabase, $created] = $this->ensureTestDatabaseExists($database);

                    $this->switchToDatabase($testDatabase);

                    if (isset($uses[Testing\DatabaseTransactions::class])) {
                        $this->ensureSchemaIsUpToDate();
                    }

                    if ($created) {
                        ParallelTesting::callSetUpTestDatabaseCallbacks($testDatabase);
                    }
                });
            }
        });

        ParallelTesting::tearDownProcess(function () {
            $this->whenNotUsingInMemoryDatabase(function ($database) {
                if (ParallelTesting::option('drop_databases')) {
                    Schema::dropDatabaseIfExists(
                        $this->testDatabase($database)
                    );
                }
            });
        });
    }

    /**
     * Ensure a test database exists and returns its name.
	 * 确保存在测试数据库并返回其名称
     *
     * @param  string  $database
     * @return array
     */
    protected function ensureTestDatabaseExists($database)
    {
        $testDatabase = $this->testDatabase($database);

        try {
            $this->usingDatabase($testDatabase, function () {
                Schema::hasTable('dummy');
            });
        } catch (QueryException $e) {
            $this->usingDatabase($database, function () use ($testDatabase) {
                Schema::dropDatabaseIfExists($testDatabase);
                Schema::createDatabase($testDatabase);
            });

            return [$testDatabase, true];
        }

        return [$testDatabase, false];
    }

    /**
     * Ensure the current database test schema is up to date.
	 * 确保当前的数据库测试模式是最新的
     *
     * @return void
     */
    protected function ensureSchemaIsUpToDate()
    {
        if (! static::$schemaIsUpToDate) {
            Artisan::call('migrate');

            static::$schemaIsUpToDate = true;
        }
    }

    /**
     * Runs the given callable using the given database.
	 * 使用给定的数据库运行给定的可调用对象
     *
     * @param  string  $database
     * @param  callable  $callable
     * @return void
     */
    protected function usingDatabase($database, $callable)
    {
        $original = DB::getConfig('database');

        try {
            $this->switchToDatabase($database);
            $callable();
        } finally {
            $this->switchToDatabase($original);
        }
    }

    /**
     * Apply the given callback when tests are not using in memory database.
	 * 当测试未在内存数据库中使用时，应用给定的回调。
     *
     * @param  callable  $callback
     * @return void
     */
    protected function whenNotUsingInMemoryDatabase($callback)
    {
        $database = DB::getConfig('database');

        if ($database !== ':memory:') {
            $callback($database);
        }
    }

    /**
     * Switch to the given database.
	 * 切换到给定的数据库
     *
     * @param  string  $database
     * @return void
     */
    protected function switchToDatabase($database)
    {
        DB::purge();

        $default = config('database.default');

        $url = config("database.connections.{$default}.url");

        if ($url) {
            config()->set(
                "database.connections.{$default}.url",
                preg_replace('/^(.*)(\/[\w-]*)(\??.*)$/', "$1/{$database}$3", $url),
            );
        } else {
            config()->set(
                "database.connections.{$default}.database",
                $database,
            );
        }
    }

    /**
     * Returns the test database name.
	 * 返回测试数据库名称
     *
     * @return string
     */
    protected function testDatabase($database)
    {
        $token = ParallelTesting::token();

        return "{$database}_test_{$token}";
    }
}
