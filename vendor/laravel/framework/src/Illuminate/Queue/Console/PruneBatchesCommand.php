<?php
/**
 * Illuminate，队列，控制台，queue:prune-batches 批量修剪命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Bus\BatchRepository;
use Illuminate\Bus\DatabaseBatchRepository;
use Illuminate\Bus\PrunableBatchRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:prune-batches')]
class PruneBatchesCommand extends Command
{
    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'queue:prune-batches
                {--hours=24 : The number of hours to retain batch data}
                {--unfinished= : The number of hours to retain unfinished batch data }
                {--cancelled= : The number of hours to retain cancelled batch data }';

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
    protected static $defaultName = 'queue:prune-batches';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Prune stale entries from the batches database';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $repository = $this->laravel[BatchRepository::class];

        $count = 0;

        if ($repository instanceof PrunableBatchRepository) {
            $count = $repository->prune(Carbon::now()->subHours($this->option('hours')));
        }

        $this->components->info("{$count} entries deleted.");

        if ($this->option('unfinished')) {
            $count = 0;

            if ($repository instanceof DatabaseBatchRepository) {
                $count = $repository->pruneUnfinished(Carbon::now()->subHours($this->option('unfinished')));
            }

            $this->components->info("{$count} unfinished entries deleted.");
        }

        if ($this->option('cancelled')) {
            $count = 0;

            if ($repository instanceof DatabaseBatchRepository) {
                $count = $repository->pruneCancelled(Carbon::now()->subHours($this->option('cancelled')));
            }

            $this->components->info("{$count} cancelled entries deleted.");
        }
    }
}
