<?php
/**
 * Illuminate，缓存，控制台，缓存表命令
 */

namespace Illuminate\Cache\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'cache:table')]
class CacheTableCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'cache:table';

    /**
     * The name of the console command.
	 * 控制台命令描述
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令。
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'cache:table';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a migration for the cache database table';

    /**
     * The filesystem instance.
	 * 文件系统实例
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new cache table command instance.
	 * 创建一个新的缓存表命令实例
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $fullPath = $this->createBaseMigration();

        $this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/cache.stub'));

        $this->components->info('Migration created successfully.');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
	 * 为表创建一个基本迁移文件
     *
     * @return string
     */
    protected function createBaseMigration()
    {
        $name = 'create_cache_table';

        $path = $this->laravel->databasePath().'/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }
}
