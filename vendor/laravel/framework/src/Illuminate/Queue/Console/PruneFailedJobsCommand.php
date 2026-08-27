<?php
/**
 * Illuminate，队列，控制台，queue:prune-failed 修剪失败的任务命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Console\Command;
use Illuminate\Queue\Failed\PrunableFailedJobProvider;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:prune-failed')]
class PruneFailedJobsCommand extends Command
{
    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'queue:prune-failed
                {--hours=24 : The number of hours to retain failed jobs data}';

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
    protected static $defaultName = 'queue:prune-failed';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Prune stale entries from the failed jobs table';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return int|null
     */
    public function handle()
    {
        $failer = $this->laravel['queue.failer'];

        if ($failer instanceof PrunableFailedJobProvider) {
            $count = $failer->prune(Carbon::now()->subHours($this->option('hours')));
        } else {
            $this->components->error('The ['.class_basename($failer).'] failed job storage driver does not support pruning.');

            return 1;
        }

        $this->components->info("{$count} entries deleted.");
    }
}
