<?php
/**
 * Illuminate，基础，控制台，clear-compiled清除编译命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'clear-compiled')]
class ClearCompiledCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'clear-compiled';

    /**
     * The name of the console command.
	 * 控制台命令名称
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'clear-compiled';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Remove the compiled class file';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        if (is_file($servicesPath = $this->laravel->getCachedServicesPath())) {
            @unlink($servicesPath);
        }

        if (is_file($packagesPath = $this->laravel->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }

        $this->components->info('Compiled services and packages files removed successfully.');
    }
}
