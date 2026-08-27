<?php
/**
 * Illuminate，队列，控制台，queue:forget 忘记失败命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:forget')]
class ForgetFailedCommand extends Command
{
    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'queue:forget {id : The ID of the failed job}';

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
    protected static $defaultName = 'queue:forget';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Delete a failed queue job';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        if ($this->laravel['queue.failer']->forget($this->argument('id'))) {
            $this->components->info('Failed job deleted successfully.');
        } else {
            $this->components->error('No failed job matches the given ID.');
        }
    }
}
