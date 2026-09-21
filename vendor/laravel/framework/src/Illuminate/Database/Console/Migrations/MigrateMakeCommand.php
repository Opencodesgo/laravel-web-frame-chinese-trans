<?php
/**
 * Illuminate，数据库，控制台，监督指令
 */

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class MigrateMakeCommand extends BaseCommand implements PromptsForMissingInput
{
    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}';

    /**
     * The console command description.
	 * console命令说明
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * The migration creator instance.
	 * 迁移创建器实例
     *
     * @var \Illuminate\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * The Composer instance.
	 * Composer实例
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new migration install command instance.
	 * 创建一个新的迁移安装命令实例
     *
     * @param  \Illuminate\Database\Migrations\MigrationCreator  $creator
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        parent::__construct();

        $this->creator = $creator;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
	 * 执行console命令
     *
     * @return void
     */
    public function handle()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
		// 开发人员可以在其中指定要修改的表。
        $name = Str::snake(trim($this->input->getArgument('name')));

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create') ?: false;

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
		// 如果没有给出表作为选项，但给出了创建选项。
        if (! $table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        // Next, we will attempt to guess the table name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new tables for the application.
        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $table, $create);

        $this->composer->dumpAutoloads();
    }

    /**
     * Write the migration file to disk.
	 * 将迁移文件写入磁盘
     *
     * @param  string  $name
     * @param  string  $table
     * @param  bool  $create
     * @return string
     */
    protected function writeMigration($name, $table, $create)
    {
        $file = $this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        );

        $this->components->info(sprintf('Migration [%s] created successfully.', $file));
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
	 * 获取迁移路径（由‘——path’选项指定或默认位置）
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                            ? $this->laravel->basePath().'/'.$targetPath
                            : $targetPath;
        }

        return parent::getMigrationPath();
    }

    /**
     * Prompt for missing input arguments using the returned questions.
	 * 使用返回的问题提示缺少输入参数
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'name' => 'What should the migration be named?',
        ];
    }
}
