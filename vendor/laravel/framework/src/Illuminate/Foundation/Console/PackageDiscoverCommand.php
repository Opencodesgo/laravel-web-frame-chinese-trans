<?php
/**
 * Illuminate，基础，控制台，package:discover 包发现命令
 */


namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\PackageManifest;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'package:discover')]
class PackageDiscoverCommand extends Command
{
    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'package:discover';

    /**
     * The name of the console command.
	 * 控制台e命令的名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'package:discover';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Rebuild the cached package manifest';

    /**
     * Execute the console command.
	 * 执行console命
     *
     * @param  \Illuminate\Foundation\PackageManifest  $manifest
     * @return void
     */
    public function handle(PackageManifest $manifest)
    {
        $this->components->info('Discovering packages');

        $manifest->build();

        collect($manifest->manifest)
            ->keys()
            ->each(fn ($description) => $this->components->task($description))
            ->whenNotEmpty(fn () => $this->newLine());
    }
}
