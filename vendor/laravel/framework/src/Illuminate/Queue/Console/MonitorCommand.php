<?php
/**
 * Illuminate，队列，控制台，queue:monitor 监督命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:monitor')]
class MonitorCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'queue:monitor
                       {queues : The names of the queues to monitor}
                       {--max=1000 : The maximum number of jobs that can be on the queue before an event is dispatched}';

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
    protected static $defaultName = 'queue:monitor';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Monitor the size of the specified queues';

    /**
     * The queue manager instance.
	 * 队列管理器实例
     *
     * @var \Illuminate\Contracts\Queue\Factory
     */
    protected $manager;

    /**
     * The events dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The table headers for the command.
	 * 命令的表头
     *
     * @var string[]
     */
    protected $headers = ['Connection', 'Queue', 'Size', 'Status'];

    /**
     * Create a new queue monitor command.
	 * 创建一个新的队列监视器命令
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $manager
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(Factory $manager, Dispatcher $events)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->events = $events;
    }

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        $queues = $this->parseQueues($this->argument('queues'));

        $this->displaySizes($queues);

        $this->dispatchEvents($queues);
    }

    /**
     * Parse the queues into an array of the connections and queues.
	 * 将队列解析为连接和队列的数组
     *
     * @param  string  $queues
     * @return \Illuminate\Support\Collection
     */
    protected function parseQueues($queues)
    {
        return collect(explode(',', $queues))->map(function ($queue) {
            [$connection, $queue] = array_pad(explode(':', $queue, 2), 2, null);

            if (! isset($queue)) {
                $queue = $connection;
                $connection = $this->laravel['config']['queue.default'];
            }

            return [
                'connection' => $connection,
                'queue' => $queue,
                'size' => $size = $this->manager->connection($connection)->size($queue),
                'status' => $size >= $this->option('max') ? '<fg=yellow;options=bold>ALERT</>' : '<fg=green;options=bold>OK</>',
            ];
        });
    }

    /**
     * Display the queue sizes in the console.
	 * 在控制台中显示队列大小
     *
     * @param  \Illuminate\Support\Collection  $queues
     * @return void
     */
    protected function displaySizes(Collection $queues)
    {
        $this->newLine();

        $this->components->twoColumnDetail('<fg=gray>Queue name</>', '<fg=gray>Size / Status</>');

        $queues->each(function ($queue) {
            $status = '['.$queue['size'].'] '.$queue['status'];

            $this->components->twoColumnDetail($queue['queue'], $status);
        });

        $this->newLine();
    }

    /**
     * Fire the monitoring events.
	 * 触发监视事件
     *
     * @param  \Illuminate\Support\Collection  $queues
     * @return void
     */
    protected function dispatchEvents(Collection $queues)
    {
        foreach ($queues as $queue) {
            if ($queue['status'] == '<fg=green;options=bold>OK</>') {
                continue;
            }

            $this->events->dispatch(
                new QueueBusy(
                    $queue['connection'],
                    $queue['queue'],
                    $queue['size'],
                )
            );
        }
    }
}
