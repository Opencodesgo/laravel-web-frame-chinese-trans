<?php
/**
 * Illuminate，数据库，控制台，回滚指令
 */

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

class RollbackCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * The console command name.
	 * console命令名称
     *
     * @var string
     */
    protected $name = 'migrate:rollback';

    /**
     * The console command description.
	 * console命令描述
     *
     * @var string
     */
    protected $description = 'Rollback the last database migration';

    /**
     * The migrator instance.
	 * 迁移器实例
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    /**
     * Create a new migration rollback command instance.
	 * 创建新的迁移回滚命令实例
     *
     * @param  \Illuminate\Database\Migrations\Migrator  $migrator
     * @return void
     */
    public function __construct(Migrator $migrator)
    {
        parent::__construct();

        $this->migrator = $migrator;
    }

    /**
     * Execute the console command.
	 * 执行console命令
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->migrator->usingConnection($this->option('database'), function () {
            $this->migrator->setOutput($this->output)->rollback(
                $this->getMigrationPaths(), [
                    'pretend' => $this->option('pretend'),
                    'step' => (int) $this->option('step'),
                ]
            );
        });

        return 0;
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

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],

            ['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed'],

            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],

            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run'],

            ['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted'],
        ];
    }
}
