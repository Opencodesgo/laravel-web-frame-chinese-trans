<?php
/**
 * Illuminate，队列，控制台，queue:retry-batch 批量重试命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Bus\BatchRepository;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:retry-batch')]
class RetryBatchCommand extends Command
{
    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'queue:retry-batch {id : The ID of the batch whose failed jobs should be retried}';

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
    protected static $defaultName = 'queue:retry-batch';

    /**
     * The console command description.
	 * 控制台命令说明
     *
     * @var string
     */
    protected $description = 'Retry the failed jobs for a batch';

    /**
     * Execute the console command.
	 * 运行控制台命令
     *
     * @return int|null
     */
    public function handle()
    {
        $batch = $this->laravel[BatchRepository::class]->find($id = $this->argument('id'));

        if (! $batch) {
            $this->components->error("Unable to find a batch with ID [{$id}].");

            return 1;
        } elseif (empty($batch->failedJobIds)) {
            $this->components->error('The given batch does not contain any failed jobs.');

            return 1;
        }

        $this->components->info("Pushing failed queue jobs of the batch [$id] back onto the queue.");

        foreach ($batch->failedJobIds as $failedJobId) {
            $this->components->task($failedJobId, fn () => $this->callSilent('queue:retry', ['id' => $failedJobId]) == 0);
        }

        $this->newLine();
    }
}
