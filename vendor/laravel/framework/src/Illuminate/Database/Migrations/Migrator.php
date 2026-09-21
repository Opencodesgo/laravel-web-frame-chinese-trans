<?php
/**
 * Illuminate，数据库，迁移，移居者
 */

namespace Illuminate\Database\Migrations;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use Illuminate\Console\View\Components\BulletList;
use Illuminate\Console\View\Components\Error;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Task;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

class Migrator
{
    /**
     * The event dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The migration repository implementation.
	 * 迁移存储库实现
     *
     * @var \Illuminate\Database\Migrations\MigrationRepositoryInterface
     */
    protected $repository;

    /**
     * The filesystem instance.
	 * 文件系统实例
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The connection resolver instance.
	 * 连接解析器实例
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The custom connection resolver callback.
	 * 自定义连接解析器回调
     *
     * @var \Closure|null
     */
    protected static $connectionResolverCallback;

    /**
     * The name of the default connection.
	 * 默认连接的名称
     *
     * @var string
     */
    protected $connection;

    /**
     * The paths to all of the migration files.
	 * 所有迁移文件的路径
     *
     * @var array
     */
    protected $paths = [];

    /**
     * The paths that have already been required.
	 * 已经需要的路径
     *
     * @var array<string, \Illuminate\Database\Migrations\Migration|null>
     */
    protected static $requiredPathCache = [];

    /**
     * The output interface implementation.
	 * 输出接口实现
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Create a new migrator instance.
	 * 创建一个新的迁移器实例
     *
     * @param  \Illuminate\Database\Migrations\MigrationRepositoryInterface  $repository
     * @param  \Illuminate\Database\ConnectionResolverInterface  $resolver
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Contracts\Events\Dispatcher|null  $dispatcher
     * @return void
     */
    public function __construct(MigrationRepositoryInterface $repository,
                                Resolver $resolver,
                                Filesystem $files,
                                Dispatcher $dispatcher = null)
    {
        $this->files = $files;
        $this->events = $dispatcher;
        $this->resolver = $resolver;
        $this->repository = $repository;
    }

    /**
     * Run the pending migrations at a given path.
	 * 在给定路径上运行挂起的迁
     *
     * @param  array|string  $paths
     * @param  array  $options
     * @return array
     */
    public function run($paths = [], array $options = [])
    {
        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
		// 一旦我们获取了路径的所有迁移文件，
        $files = $this->getMigrationFiles($paths);

        $this->requireFiles($migrations = $this->pendingMigrations(
            $files, $this->repository->getRan()
        ));

        // Once we have all these migrations that are outstanding we are ready to run
        // we will go ahead and run them "up". This will execute each migration as
        // an operation against a database. Then we'll return this list of them.
		// 旦我们有了这些杰出的迁移，
        $this->runPending($migrations, $options);

        return $migrations;
    }

    /**
     * Get the migration files that have not yet run.
	 * 获取尚未运行的迁移文件
     *
     * @param  array  $files
     * @param  array  $ran
     * @return array
     */
    protected function pendingMigrations($files, $ran)
    {
        return Collection::make($files)
                ->reject(function ($file) use ($ran) {
                    return in_array($this->getMigrationName($file), $ran);
                })->values()->all();
    }

    /**
     * Run an array of migrations.
	 * 运行一系列迁移
     *
     * @param  array  $migrations
     * @param  array  $options
     * @return void
     */
    public function runPending(array $migrations, array $options = [])
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
		// 首先，我们要确保有任何迁移要运行。
        if (count($migrations) === 0) {
            $this->fireMigrationEvent(new NoPendingMigrations('up'));

            $this->write(Info::class, 'Nothing to migrate');

            return;
        }

        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution. We will also extract a few of the options.
		// 接下来，我们将获得迁移的下一个批号
        $batch = $this->repository->getNextBatchNumber();

        $pretend = $options['pretend'] ?? false;

        $step = $options['step'] ?? false;

        $this->fireMigrationEvent(new MigrationsStarted('up'));

        $this->write(Info::class, 'Running migrations.');

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
		// 一旦我们有了一系列的迁徙，我们会穿过它们。
        foreach ($migrations as $file) {
            $this->runUp($file, $batch, $pretend);

            if ($step) {
                $batch++;
            }
        }

        $this->fireMigrationEvent(new MigrationsEnded('up'));

        if ($this->output) {
            $this->output->writeln('');
        }
    }

    /**
     * Run "up" a migration instance.
	 * 运行"up"迁移实例
     *
     * @param  string  $file
     * @param  int  $batch
     * @param  bool  $pretend
     * @return void
     */
    protected function runUp($file, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
		// 首先，我们将从中解析一个迁移类的"真实"实例从迁移文件名。
        $migration = $this->resolvePath($file);

        $name = $this->getMigrationName($file);

        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        $this->write(Task::class, $name, fn () => $this->runMigration($migration, 'up'));

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
		// 一旦我们运行了迁移类，我们将记录它在此运行。
        $this->repository->log($name, $batch);
    }

    /**
     * Rollback the last migration operation.
	 * 回滚上一次迁移操作
     *
     * @param  array|string  $paths
     * @param  array  $options
     * @return array
     */
    public function rollback($paths = [], array $options = [])
    {
        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation. We'll then reverse those migrations and run each
        // of them "down" to reverse the last migration "operation" which ran.
		// 我们想要拉入在前一批迁移上运行的最后一批迁移。
        $migrations = $this->getMigrationsForRollback($options);

        if (count($migrations) === 0) {
            $this->fireMigrationEvent(new NoPendingMigrations('down'));

            $this->write(Info::class, 'Nothing to rollback.');

            return [];
        }

        return tap($this->rollbackMigrations($migrations, $paths, $options), function () {
            if ($this->output) {
                $this->output->writeln('');
            }
        });
    }

    /**
     * Get the migrations for a rollback operation.
	 * 获取回滚操作的迁移
     *
     * @param  array  $options
     * @return array
     */
    protected function getMigrationsForRollback(array $options)
    {
        if (($steps = $options['step'] ?? 0) > 0) {
            return $this->repository->getMigrations($steps);
        }

        return $this->repository->getLast();
    }

    /**
     * Rollback the given migrations.
	 * 回滚给定的迁移
     *
     * @param  array  $migrations
     * @param  array|string  $paths
     * @param  array  $options
     * @return array
     */
    protected function rollbackMigrations(array $migrations, $paths, array $options)
    {
        $rolledBack = [];

        $this->requireFiles($files = $this->getMigrationFiles($paths));

        $this->fireMigrationEvent(new MigrationsStarted('down'));

        $this->write(Info::class, 'Rolling back migrations.');

        // Next we will run through all of the migrations and call the "down" method
        // which will reverse each migration in order. This getLast method on the
        // repository already returns these migration's names in reverse order.
		// 接下来，我们将遍历所有迁移并调用"down"方法
        foreach ($migrations as $migration) {
            $migration = (object) $migration;

            if (! $file = Arr::get($files, $migration->migration)) {
                $this->write(TwoColumnDetail::class, $migration->migration, '<fg=yellow;options=bold>Migration not found</>');

                continue;
            }

            $rolledBack[] = $file;

            $this->runDown(
                $file, $migration,
                $options['pretend'] ?? false
            );
        }

        $this->fireMigrationEvent(new MigrationsEnded('down'));

        return $rolledBack;
    }

    /**
     * Rolls all of the currently applied migrations back.
	 * 回滚所有当前应用的迁移
     *
     * @param  array|string  $paths
     * @param  bool  $pretend
     * @return array
     */
    public function reset($paths = [], $pretend = false)
    {
        // Next, we will reverse the migration list so we can run them back in the
        // correct order for resetting this database. This will allow us to get
        // the database back into its "empty" state ready for the migrations.
		// 接下来，我们将反转迁移列表。
        $migrations = array_reverse($this->repository->getRan());

        if (count($migrations) === 0) {
            $this->write(Info::class, 'Nothing to rollback.');

            return [];
        }

        return tap($this->resetMigrations($migrations, $paths, $pretend), function () {
            if ($this->output) {
                $this->output->writeln('');
            }
        });
    }

    /**
     * Reset the given migrations.
	 * 重置给定的迁移
     *
     * @param  array  $migrations
     * @param  array  $paths
     * @param  bool  $pretend
     * @return array
     */
    protected function resetMigrations(array $migrations, array $paths, $pretend = false)
    {
        // Since the getRan method that retrieves the migration name just gives us the
        // migration name, we will format the names into objects with the name as a
        // property on the objects so that we can pass it to the rollback method.
		// 因为检索迁移名称的getRan方法只给我们提供。
        $migrations = collect($migrations)->map(function ($m) {
            return (object) ['migration' => $m];
        })->all();

        return $this->rollbackMigrations(
            $migrations, $paths, compact('pretend')
        );
    }

    /**
     * Run "down" a migration instance.
	 * 运行"down"迁移实例
     *
     * @param  string  $file
     * @param  object  $migration
     * @param  bool  $pretend
     * @return void
     */
    protected function runDown($file, $migration, $pretend)
    {
        // First we will get the file name of the migration so we can resolve out an
        // instance of the migration. Once we get an instance we can either run a
        // pretend execution of the migration or we can run the real migration.
		// 首先，我们将获得迁移的文件名。
        $instance = $this->resolvePath($file);

        $name = $this->getMigrationName($file);

        if ($pretend) {
            return $this->pretendToRun($instance, 'down');
        }

        $this->write(Task::class, $name, fn () => $this->runMigration($instance, 'down'));

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
		// 一旦我们成功地"向下"运行迁移，
        $this->repository->delete($migration);
    }

    /**
     * Run a migration inside a transaction if the database supports it.
	 * 如果数据库支持，则在事务中运行迁移。
     *
     * @param  object  $migration
     * @param  string  $method
     * @return void
     */
    protected function runMigration($migration, $method)
    {
        $connection = $this->resolveConnection(
            $migration->getConnection()
        );

        $callback = function () use ($connection, $migration, $method) {
            if (method_exists($migration, $method)) {
                $this->fireMigrationEvent(new MigrationStarted($migration, $method));

                $this->runMethod($connection, $migration, $method);

                $this->fireMigrationEvent(new MigrationEnded($migration, $method));
            }
        };

        $this->getSchemaGrammar($connection)->supportsSchemaTransactions()
            && $migration->withinTransaction
                    ? $connection->transaction($callback)
                    : $callback();
    }

    /**
     * Pretend to run the migrations.
	 * 假装运行迁移
     *
     * @param  object  $migration
     * @param  string  $method
     * @return void
     */
    protected function pretendToRun($migration, $method)
    {
        try {
            $name = get_class($migration);

            $reflectionClass = new ReflectionClass($migration);

            if ($reflectionClass->isAnonymous()) {
                $name = $this->getMigrationName($reflectionClass->getFileName());
            }

            $this->write(TwoColumnDetail::class, $name);
            $this->write(BulletList::class, collect($this->getQueries($migration, $method))->map(function ($query) {
                return $query['query'];
            }));
        } catch (SchemaException $e) {
            $name = get_class($migration);

            $this->write(Error::class, sprintf(
                '[%s] failed to dump queries. This may be due to changing database columns using Doctrine, which is not supported while pretending to run migrations.',
                $name,
            ));
        }
    }

    /**
     * Get all of the queries that would be run for a migration.
	 * 获取将为迁移运行的所有查询
     *
     * @param  object  $migration
     * @param  string  $method
     * @return array
     */
    protected function getQueries($migration, $method)
    {
        // Now that we have the connections we can resolve it and pretend to run the
        // queries against the database returning the array of raw SQL statements
        // that would get fired against the database system for this migration.
        $db = $this->resolveConnection(
            $migration->getConnection()
        );

        return $db->pretend(function () use ($db, $migration, $method) {
            if (method_exists($migration, $method)) {
                $this->runMethod($db, $migration, $method);
            }
        });
    }

    /**
     * Run a migration method on the given connection.
	 * 在给定的连接上运行迁移方法
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  object  $migration
     * @param  string  $method
     * @return void
     */
    protected function runMethod($connection, $migration, $method)
    {
        $previousConnection = $this->resolver->getDefaultConnection();

        try {
            $this->resolver->setDefaultConnection($connection->getName());

            $migration->{$method}();
        } finally {
            $this->resolver->setDefaultConnection($previousConnection);
        }
    }

    /**
     * Resolve a migration instance from a file.
	 * 从文件解析迁移实例
     *
     * @param  string  $file
     * @return object
     */
    public function resolve($file)
    {
        $class = $this->getMigrationClass($file);

        return new $class;
    }

    /**
     * Resolve a migration instance from a migration path.
	 * 从迁移路径解析迁移实例
     *
     * @param  string  $path
     * @return object
     */
    protected function resolvePath(string $path)
    {
        $class = $this->getMigrationClass($this->getMigrationName($path));

        if (class_exists($class) && realpath($path) == (new ReflectionClass($class))->getFileName()) {
            return new $class;
        }

        $migration = static::$requiredPathCache[$path] ??= $this->files->getRequire($path);

        if (is_object($migration)) {
            return method_exists($migration, '__construct')
                    ? $this->files->getRequire($path)
                    : clone $migration;
        }

        return new $class;
    }

    /**
     * Generate a migration class name based on the migration file name.
	 * 根据迁移文件名生成迁移类名
     *
     * @param  string  $migrationName
     * @return string
     */
    protected function getMigrationClass(string $migrationName): string
    {
        return Str::studly(implode('_', array_slice(explode('_', $migrationName), 4)));
    }

    /**
     * Get all of the migration files in a given path.
	 * 获取给定路径中的所有迁移文件
     *
     * @param  string|array  $paths
     * @return array
     */
    public function getMigrationFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return str_ends_with($path, '.php') ? [$path] : $this->files->glob($path.'/*_*.php');
        })->filter()->values()->keyBy(function ($file) {
            return $this->getMigrationName($file);
        })->sortBy(function ($file, $key) {
            return $key;
        })->all();
    }

    /**
     * Require in all the migration files in a given path.
	 * 在给定路径中的所有迁移文件中要求
     *
     * @param  array  $files
     * @return void
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }

    /**
     * Get the name of the migration.
	 * 获取迁移的名称
     *
     * @param  string  $path
     * @return string
     */
    public function getMigrationName($path)
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Register a custom migration path.
	 * 注册自定义迁移路径
     *
     * @param  string  $path
     * @return void
     */
    public function path($path)
    {
        $this->paths = array_unique(array_merge($this->paths, [$path]));
    }

    /**
     * Get all of the custom migration paths.
	 * 获取所有自定义迁移路径
     *
     * @return array
     */
    public function paths()
    {
        return $this->paths;
    }

    /**
     * Get the default connection name.
	 * 得到默认连接名称
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute the given callback using the given connection as the default connection.
	 * 使用给定的连接作为默认连接执行给定的回调
     *
     * @param  string  $name
     * @param  callable  $callback
     * @return mixed
     */
    public function usingConnection($name, callable $callback)
    {
        $previousConnection = $this->resolver->getDefaultConnection();

        $this->setConnection($name);

        return tap($callback(), function () use ($previousConnection) {
            $this->setConnection($previousConnection);
        });
    }

    /**
     * Set the default connection name.
	 * 设置默认连接名称
     *
     * @param  string  $name
     * @return void
     */
    public function setConnection($name)
    {
        if (! is_null($name)) {
            $this->resolver->setDefaultConnection($name);
        }

        $this->repository->setSource($name);

        $this->connection = $name;
    }

    /**
     * Resolve the database connection instance.
	 * 解析数据库连接实例
     *
     * @param  string  $connection
     * @return \Illuminate\Database\Connection
     */
    public function resolveConnection($connection)
    {
        if (static::$connectionResolverCallback) {
            return call_user_func(
                static::$connectionResolverCallback,
                $this->resolver,
                $connection ?: $this->connection
            );
        } else {
            return $this->resolver->connection($connection ?: $this->connection);
        }
    }

    /**
     * Set a connection resolver callback.
	 * 设置连接解析器回调
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function resolveConnectionsUsing(Closure $callback)
    {
        static::$connectionResolverCallback = $callback;
    }

    /**
     * Get the schema grammar out of a migration connection.
	 * 从迁移连接中获取模式语法
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected function getSchemaGrammar($connection)
    {
        if (is_null($grammar = $connection->getSchemaGrammar())) {
            $connection->useDefaultSchemaGrammar();

            $grammar = $connection->getSchemaGrammar();
        }

        return $grammar;
    }

    /**
     * Get the migration repository instance.
	 * 获取迁移存储库实例
     *
     * @return \Illuminate\Database\Migrations\MigrationRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Determine if the migration repository exists.
	 * 确定迁移存储库是否存在
     *
     * @return bool
     */
    public function repositoryExists()
    {
        return $this->repository->repositoryExists();
    }

    /**
     * Determine if any migrations have been run.
	 * 确定是否运行了任何迁移
     *
     * @return bool
     */
    public function hasRunAnyMigrations()
    {
        return $this->repositoryExists() && count($this->repository->getRan()) > 0;
    }

    /**
     * Delete the migration repository data store.
	 * 删除迁移存储库数据存储
     *
     * @return void
     */
    public function deleteRepository()
    {
        return $this->repository->deleteRepository();
    }

    /**
     * Get the file system instance.
	 * 获取文件系统实例
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Set the output implementation that should be used by the console.
	 * 设置控制台应该使用的输出实现
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Write to the console's output.
	 * 写入控制台的输出
     *
     * @param  string  $component
     * @param  array<int, string>|string  ...$arguments
     * @return void
     */
    protected function write($component, ...$arguments)
    {
        if ($this->output && class_exists($component)) {
            (new $component($this->output))->render(...$arguments);
        } else {
            foreach ($arguments as $argument) {
                if (is_callable($argument)) {
                    $argument();
                }
            }
        }
    }

    /**
     * Fire the given event for the migration.
	 * 为迁移触发给定的事件
     *
     * @param  \Illuminate\Contracts\Database\Events\MigrationEvent  $event
     * @return void
     */
    public function fireMigrationEvent($event)
    {
        if ($this->events) {
            $this->events->dispatch($event);
        }
    }
}
