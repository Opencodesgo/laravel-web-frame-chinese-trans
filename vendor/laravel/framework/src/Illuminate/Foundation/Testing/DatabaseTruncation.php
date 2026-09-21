<?php
/**
 * Illuminate，基础，测试，数据库截断
 */

namespace Illuminate\Foundation\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\Traits\CanConfigureMigrationCommands;

trait DatabaseTruncation
{
    use CanConfigureMigrationCommands;

    /**
     * The cached names of the database tables for each connection.
	 * 为每个连接缓存的数据库表的名称
     *
     * @var array
     */
    protected static array $allTables;

    /**
     * Truncate the database tables for all configured connections.
	 * 截断所有已配置连接的数据库表
     *
     * @return void
     */
    protected function truncateDatabaseTables(): void
    {
        // Migrate and seed the database on first run...
		// 在第一次运行时迁移和播种数据库...
        if (! RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', $this->migrateFreshUsing());

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;

            return;
        }

        // Always clear any test data on subsequent runs...
		// 在后续运行中始终清除所有测试数据...
        $this->truncateTablesForAllConnections();

        if ($seeder = $this->seeder()) {
            // Use a specific seeder class...
            $this->artisan('db:seed', ['--class' => $seeder]);
        } elseif ($this->shouldSeed()) {
            // Use the default seeder class...
            $this->artisan('db:seed');
        }
    }

    /**
     * Truncate the database tables for all configured connections.
	 * 截断所有已配置连接的数据库表
     *
     * @return void
     */
    protected function truncateTablesForAllConnections(): void
    {
        $database = $this->app->make('db');

        collect($this->connectionsToTruncate())
            ->each(function ($name) use ($database) {
                $connection = $database->connection($name);

                $connection->getSchemaBuilder()->withoutForeignKeyConstraints(
                    fn () => $this->truncateTablesForConnection($connection, $name)
                );
            });
    }

    /**
     * Truncate the database tables for the given database connection.
	 * 截断给定数据库连接的数据库表
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string|null  $name
     * @return void
     */
    protected function truncateTablesForConnection(ConnectionInterface $connection, ?string $name): void
    {
        $dispatcher = $connection->getEventDispatcher();

        $connection->unsetEventDispatcher();

        collect(static::$allTables[$name] ??= $connection->getDoctrineSchemaManager()->listTableNames())
            ->when(
                property_exists($this, 'tablesToTruncate'),
                fn ($tables) => $tables->intersect($this->tablesToTruncate),
                fn ($tables) => $tables->diff($this->exceptTables($name))
            )
            ->filter(fn ($table) => $connection->table($table)->exists())
            ->each(fn ($table) => $connection->table($table)->truncate());

        $connection->setEventDispatcher($dispatcher);
    }

    /**
     * The database connections that should have their tables truncated.
	 * 应该截断其表的数据库连接
     *
     * @return array
     */
    protected function connectionsToTruncate(): array
    {
        return property_exists($this, 'connectionsToTruncate')
                    ? $this->connectionsToTruncate : [null];
    }

    /**
     * Get the tables that should not be truncated.
	 * 获取不应被截断的表
     *
     * @param  string|null  $connectionName
     * @return array
     */
    protected function exceptTables(?string $connectionName): array
    {
        if (property_exists($this, 'exceptTables')) {
            return array_merge(
                $this->exceptTables[$connectionName] ?? [],
                [$this->app['config']->get('database.migrations')]
            );
        }

        return [$this->app['config']->get('database.migrations')];
    }
}
