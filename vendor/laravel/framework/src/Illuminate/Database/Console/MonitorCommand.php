<?php
/**
 * Illuminate，数据库，控制台，监督指令
 */

namespace Illuminate\Database\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Events\DatabaseBusy;
use Illuminate\Support\Composer;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:monitor')]
class MonitorCommand extends DatabaseInspectionCommand
{
    /**
     * The name and signature of the console command.
	 * console命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'db:monitor
                {--databases= : The database connections to monitor}
                {--max= : The maximum number of connections that can be open before an event is dispatched}';

    /**
     * The name of the console command.
	 * console命令的名称
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'db:monitor';

    /**
     * The console command description.
	 * console命令的说明
     *
     * @var string
     */
    protected $description = 'Monitor the number of connections on the specified database';

    /**
     * The connection resolver instance.
	 * 连接解析器实例
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $connection;

    /**
     * The events dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new command instance.
	 * 创建一个新的命令实例
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $connection
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @param  \Illuminate\Support\Composer  $composer
     */
    public function __construct(ConnectionResolverInterface $connection, Dispatcher $events, Composer $composer)
    {
        parent::__construct($composer);

        $this->connection = $connection;
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
        $databases = $this->parseDatabases($this->option('databases'));

        $this->displayConnections($databases);

        if ($this->option('max')) {
            $this->dispatchEvents($databases);
        }
    }

    /**
     * Parse the database into an array of the connections.
	 * 将数据库解析为连接数组
     *
     * @param  string  $databases
     * @return \Illuminate\Support\Collection
     */
    protected function parseDatabases($databases)
    {
        return collect(explode(',', $databases))->map(function ($database) {
            if (! $database) {
                $database = $this->laravel['config']['database.default'];
            }

            $maxConnections = $this->option('max');

            return [
                'database' => $database,
                'connections' => $connections = $this->getConnectionCount($this->connection->connection($database)),
                'status' => $maxConnections && $connections >= $maxConnections ? '<fg=yellow;options=bold>ALERT</>' : '<fg=green;options=bold>OK</>',
            ];
        });
    }

    /**
     * Display the databases and their connection counts in the console.
	 * 在控制台中显示数据库及其连接数
     *
     * @param  \Illuminate\Support\Collection  $databases
     * @return void
     */
    protected function displayConnections($databases)
    {
        $this->newLine();

        $this->components->twoColumnDetail('<fg=gray>Database name</>', '<fg=gray>Connections</>');

        $databases->each(function ($database) {
            $status = '['.$database['connections'].'] '.$database['status'];

            $this->components->twoColumnDetail($database['database'], $status);
        });

        $this->newLine();
    }

    /**
     * Dispatch the database monitoring events.
	 * 调度数据库监视事件
     *
     * @param  \Illuminate\Support\Collection  $databases
     * @return void
     */
    protected function dispatchEvents($databases)
    {
        $databases->each(function ($database) {
            if ($database['status'] === '<fg=green;options=bold>OK</>') {
                return;
            }

            $this->events->dispatch(
                new DatabaseBusy(
                    $database['database'],
                    $database['connections']
                )
            );
        });
    }
}
