<?php
/**
 * Illuminate，数据库，控制台，清除命令
 */

namespace Illuminate\Database\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'db:wipe')]
class WipeCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'db:wipe';

    /**
     * The name of the console command.
	 * 控制台命令的名称
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'db:wipe';

    /**
     * The console command description.
	 * 控制台命令的描述
     *
     * @var string
     */
    protected $description = 'Drop all tables, views, and types';

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

        $database = $this->input->getOption('database');

        if ($this->option('drop-views')) {
            $this->dropAllViews($database);

            $this->components->info('Dropped all views successfully.');
        }

        $this->dropAllTables($database);

        $this->components->info('Dropped all tables successfully.');

        if ($this->option('drop-types')) {
            $this->dropAllTypes($database);

            $this->components->info('Dropped all types successfully.');
        }

        return 0;
    }

    /**
     * Drop all of the database tables.
	 * 删除所有数据库表
     *
     * @param  string  $database
     * @return void
     */
    protected function dropAllTables($database)
    {
        $this->laravel['db']->connection($database)
                    ->getSchemaBuilder()
                    ->dropAllTables();
    }

    /**
     * Drop all of the database views.
	 * 删除所有数据库视图
     *
     * @param  string  $database
     * @return void
     */
    protected function dropAllViews($database)
    {
        $this->laravel['db']->connection($database)
                    ->getSchemaBuilder()
                    ->dropAllViews();
    }

    /**
     * Drop all of the database types.
	 * 删除所有数据库类型
     *
     * @param  string  $database
     * @return void
     */
    protected function dropAllTypes($database)
    {
        $this->laravel['db']->connection($database)
                    ->getSchemaBuilder()
                    ->dropAllTypes();
    }

    /**
     * Get the console command options.
	 * 获取控制台命令选项
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
            ['drop-views', null, InputOption::VALUE_NONE, 'Drop all tables and views'],
            ['drop-types', null, InputOption::VALUE_NONE, 'Drop all tables and types (Postgres only)'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }
}
