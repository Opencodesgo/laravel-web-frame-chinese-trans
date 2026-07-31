<?php
/**
 * Illuminate，数据库，控制台，迁移，migrate 迁移命令
 */

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\SchemaLoaded;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\SqlServerConnection;

class MigrateCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
	 * console命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'migrate {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--path=* : The path(s) to the migrations files to be executed}
                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                {--schema-path= : The path to a schema dump file}
                {--pretend : Dump the SQL queries that would be run}
                {--seed : Indicates if the seed task should be re-run}
                {--seeder= : The class name of the root seeder}
                {--step : Force the migrations to be run so they can be rolled back individually}';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Run the database migrations';

    /**
     * The migrator instance.
	 * 迁移器实例
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    /**
     * The event dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new migration command instance.
	 * 创建一个新的迁移命令实例
     *
     * @param  \Illuminate\Database\Migrations\Migrator  $migrator
     * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
     * @return void
     */
    public function __construct(Migrator $migrator, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->migrator = $migrator;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->migrator->usingConnection($this->option('database'), function () {
            $this->prepareDatabase();

            // Next, we will check to see if a path option has been defined. If it has
            // we will use the path relative to the root of this installation folder
            // so that migrations may be run for any path within the applications.
			// 接下来，我们将检查是否定义了路径选项。
			// 如果有，我们将使用相对于此安装文件夹根目录的路径，这样就可以在应用程序内的任何路径上运行迁移。
            $this->migrator->setOutput($this->output)
                    ->run($this->getMigrationPaths(), [
                        'pretend' => $this->option('pretend'),
                        'step' => $this->option('step'),
                    ]);

            // Finally, if the "seed" option has been given, we will re-run the database
            // seed task to re-populate the database, which is convenient when adding
            // a migration and a seed at the same time, as it is only this command.
			// 最后，如果给出了"seed"选项，我们将重新运行数据库种子任务。
            if ($this->option('seed') && ! $this->option('pretend')) {
                $this->call('db:seed', [
                    '--class' => $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
                    '--force' => true,
                ]);
            }
        });

        return 0;
    }

    /**
     * Prepare the migration database for running.
	 * 准备运行迁移数据库
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if (! $this->migrator->repositoryExists()) {
            $this->call('migrate:install', array_filter([
                '--database' => $this->option('database'),
            ]));
        }

        if (! $this->migrator->hasRunAnyMigrations() && ! $this->option('pretend')) {
            $this->loadSchemaState();
        }
    }

    /**
     * Load the schema state to seed the initial database schema structure.
	 * 加载模式状态以播种初始数据库模式结构
     *
     * @return void
     */
    protected function loadSchemaState()
    {
        $connection = $this->migrator->resolveConnection($this->option('database'));

        // First, we will make sure that the connection supports schema loading and that
        // the schema file exists before we proceed any further. If not, we will just
        // continue with the standard migration operation as normal without errors.
		// 首先，我们要确保连接支持模式加载，在我们继续之前，模式文件已经存在。
        if ($connection instanceof SqlServerConnection ||
            ! is_file($path = $this->schemaPath($connection))) {
            return;
        }

        $this->line('<info>Loading stored database schema:</info> '.$path);

        $startTime = microtime(true);

        // Since the schema file will create the "migrations" table and reload it to its
        // proper state, we need to delete it here so we don't get an error that this
        // table already exists when the stored database schema file gets executed.
		// 因为模式文件将创建"迁移"表并将其重新加载到它的适当状态，我们需要在这里删除它，这样我们就不会得到错误。
        $this->migrator->deleteRepository();

        $connection->getSchemaState()->handleOutputUsing(function ($type, $buffer) {
            $this->output->write($buffer);
        })->load($path);

        $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

        // Finally, we will fire an event that this schema has been loaded so developers
        // can perform any post schema load tasks that are necessary in listeners for
        // this event, which may seed the database tables with some necessary data.
		// 最后，我们将触发一个事件，表明该模式已经加载给开发人员可以执行任何模式后加载任务。
        $this->dispatcher->dispatch(
            new SchemaLoaded($connection, $path)
        );

        $this->line('<info>Loaded stored database schema.</info> ('.$runTime.'ms)');
    }

    /**
     * Get the path to the stored schema for the given connection.
	 * 获取给定连接的存储模式的路径
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return string
     */
    protected function schemaPath($connection)
    {
        if ($this->option('schema-path')) {
            return $this->option('schema-path');
        }

        if (file_exists($path = database_path('schema/'.$connection->getName().'-schema.dump'))) {
            return $path;
        }

        return database_path('schema/'.$connection->getName().'-schema.sql');
    }
}
