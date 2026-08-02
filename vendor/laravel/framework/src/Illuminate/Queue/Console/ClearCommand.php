<?php
/**
 * Illuminate，队列，控制台，queue:clear 清除命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Queue\ClearableQueue;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ClearCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'queue:clear';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Delete all of the jobs from the specified queue';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return int|null
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $connection = $this->argument('connection')
                        ?: $this->laravel['config']['queue.default'];

        // We need to get the right queue for the connection which is set in the queue
        // configuration file for the application. We will pull it based on the set
        // connection being run for the queue operation currently being executed.
		// 我们需要为连接获得正确的队列，设置在队列中的应用程序的配置文件。
        $queueName = $this->getQueue($connection);

        $queue = $this->laravel['queue']->connection($connection);

        if ($queue instanceof ClearableQueue) {
            $count = $queue->clear($queueName);

            $this->line('<info>Cleared '.$count.' jobs from the ['.$queueName.'] queue</info> ');
        } else {
            $this->line('<error>Clearing queues is not supported on ['.(new ReflectionClass($queue))->getShortName().']</error> ');
        }

        return 0;
    }

    /**
     * Get the queue name to clear.
	 * 得到要清除的队列名称
     *
     * @param  string  $connection
     * @return string
     */
    protected function getQueue($connection)
    {
        return $this->option('queue') ?: $this->laravel['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );
    }

    /**
     * Get the console command arguments.
	 * 得到控制台命令参数
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['connection', InputArgument::OPTIONAL, 'The name of the queue connection to clear'],
        ];
    }

    /**
     * Get the console command options.
	 * 得到控制台命令选项
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['queue', null, InputOption::VALUE_OPTIONAL, 'The name of the queue to clear'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }
}
