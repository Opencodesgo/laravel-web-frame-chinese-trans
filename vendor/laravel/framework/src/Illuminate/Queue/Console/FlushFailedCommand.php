<?php
/**
 * Illuminate，队列，控制台，queue:flush 刷新失败命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:flush')]
class FlushFailedCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'queue:flush {--hours= : The number of hours to retain failed job data}';

    /**
     * The name of the console command.
	 * 控制台命令名
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'queue:flush';

    /**
     * The console command description.
	 * 控制台命令说明
     *
     * @var string
     */
    protected $description = 'Flush all of the failed queue jobs';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel['queue.failer']->flush($this->option('hours'));

        if ($this->option('hours')) {
            $this->components->info("All jobs that failed more than {$this->option('hours')} hours ago have been deleted successfully.");

            return;
        }

        $this->components->info('All failed jobs deleted successfully.');
    }
}
