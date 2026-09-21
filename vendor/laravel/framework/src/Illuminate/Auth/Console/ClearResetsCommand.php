<?php
/**
 * Illuminate，认证，控制台，清除复位命令
 */

namespace Illuminate\Auth\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'auth:clear-resets')]
class ClearResetsCommand extends Command
{
    /**
     * The name and signature of the console command.
	 * 控制台命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'auth:clear-resets {name? : The name of the password broker}';

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
    protected static $defaultName = 'auth:clear-resets';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Flush expired password reset tokens';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel['auth.password']->broker($this->argument('name'))->getRepository()->deleteExpired();

        $this->components->info('Expired reset tokens cleared successfully.');
    }
}
