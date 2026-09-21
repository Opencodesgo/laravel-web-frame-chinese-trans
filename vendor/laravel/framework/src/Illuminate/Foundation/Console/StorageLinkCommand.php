<?php
/**
 * Illuminate，基础，控制台，storage:link 存储链路命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'storage:link')]
class StorageLinkCommand extends Command
{
    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'storage:link
                {--relative : Create the symbolic link using relative paths}
                {--force : Recreate existing symbolic links}';

    /**
     * The name of the console command.
	 * 控制台命令名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'storage:link';

    /**
     * The console command description.
	 * 控制台命令说明
     *
     * @var string
     */
    protected $description = 'Create the symbolic links configured for the application';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $relative = $this->option('relative');

        foreach ($this->links() as $link => $target) {
            if (file_exists($link) && ! $this->isRemovableSymlink($link, $this->option('force'))) {
                $this->components->error("The [$link] link already exists.");
                continue;
            }

            if (is_link($link)) {
                $this->laravel->make('files')->delete($link);
            }

            if ($relative) {
                $this->laravel->make('files')->relativeLink($target, $link);
            } else {
                $this->laravel->make('files')->link($target, $link);
            }

            $this->components->info("The [$link] link has been connected to [$target].");
        }
    }

    /**
     * Get the symbolic links that are configured for the application.
	 * 获取为应用程序配置的符号链接
     *
     * @return array
     */
    protected function links()
    {
        return $this->laravel['config']['filesystems.links'] ??
               [public_path('storage') => storage_path('app/public')];
    }

    /**
     * Determine if the provided path is a symlink that can be removed.
	 * 确定所提供的路径是否是可以删除的符号链接
     *
     * @param  string  $link
     * @param  bool  $force
     * @return bool
     */
    protected function isRemovableSymlink(string $link, bool $force): bool
    {
        return is_link($link) && $force;
    }
}
