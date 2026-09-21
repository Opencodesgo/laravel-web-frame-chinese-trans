<?php
/**
 * Illuminate，数据库，控制台，状态命令
 */

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

class StatusCommand extends BaseCommand
{
    /**
     * The console command name.
	 * console命令名称
     *
     * @var string
     */
    protected $name = 'migrate:status';

    /**
     * The console command description.
	 * console控制说明
     *
     * @var string
     */
    protected $description = 'Show the status of each migration';

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
     * @return int|null
     */
    public function handle()
    {
        return $this->migrator->usingConnection($this->option('database'), function () {
            if (! $this->migrator->repositoryExists()) {
                $this->components->error('Migration table not found.');

                return 1;
            }

            $ran = $this->migrator->getRepository()->getRan();

            $batches = $this->migrator->getRepository()->getMigrationBatches();

            if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
                $this->newLine();

                $this->components->twoColumnDetail('<fg=gray>Migration name</>', '<fg=gray>Batch / Status</>');

                $migrations
                    ->when($this->option('pending'), fn ($collection) => $collection->filter(function ($migration) {
                        return str($migration[1])->contains('Pending');
                    }))
                    ->each(
                        fn ($migration) => $this->components->twoColumnDetail($migration[0], $migration[1])
                    );

                $this->newLine();
            } else {
                $this->components->info('No migrations found');
            }
        });
    }

    /**
     * Get the status for the given run migrations.
	 * 获取给定运行迁移的状态
     *
     * @param  array  $ran
     * @param  array  $batches
     * @return \Illuminate\Support\Collection
     */
    protected function getStatusFor(array $ran, array $batches)
    {
        return Collection::make($this->getAllMigrationFiles())
                    ->map(function ($migration) use ($ran, $batches) {
                        $migrationName = $this->migrator->getMigrationName($migration);

                        $status = in_array($migrationName, $ran)
                            ? '<fg=green;options=bold>Ran</>'
                            : '<fg=yellow;options=bold>Pending</>';

                        if (in_array($migrationName, $ran)) {
                            $status = '['.$batches[$migrationName].'] '.$status;
                        }

                        return [$migrationName, $status];
                    });
    }

    /**
     * Get an array of all of the migration files.
	 * 获取所有迁移文件的数组
     *
     * @return array
     */
    protected function getAllMigrationFiles()
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
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
            ['pending', null, InputOption::VALUE_NONE, 'Only list pending migrations'],
            ['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to use'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
        ];
    }
}
