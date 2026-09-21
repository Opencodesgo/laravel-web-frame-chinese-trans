<?php
/**
 * Illuminate，数据库，控制台，显示命令
 */

namespace Illuminate\Database\Console;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\View;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:show')]
class ShowCommand extends DatabaseInspectionCommand
{
    /**
     * The name and signature of the console command.
	 * console命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'db:show {--database= : The database connection}
                {--json : Output the database information as JSON}
                {--counts : Show the table row count <bg=red;options=bold> Note: This can be slow on large databases </>};
                {--views : Show the database views <bg=red;options=bold> Note: This can be slow on large databases </>}';

    /**
     * The console command description.
	 * console命令的描述
     *
     * @var string
     */
    protected $description = 'Display information about the given database';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $connections
     * @return int
     */
    public function handle(ConnectionResolverInterface $connections)
    {
        if (! $this->ensureDependenciesExist()) {
            return 1;
        }

        $connection = $connections->connection($database = $this->input->getOption('database'));

        $schema = $connection->getDoctrineSchemaManager();

        $this->registerTypeMappings($schema->getDatabasePlatform());

        $data = [
            'platform' => [
                'config' => $this->getConfigFromDatabase($database),
                'name' => $this->getPlatformName($schema->getDatabasePlatform(), $database),
                'open_connections' => $this->getConnectionCount($connection),
            ],
            'tables' => $this->tables($connection, $schema),
        ];

        if ($this->option('views')) {
            $data['views'] = $this->collectViews($connection, $schema);
        }

        $this->display($data);

        return 0;
    }

    /**
     * Get information regarding the tables within the database.
	 * 获取有关数据库中表的信息
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Doctrine\DBAL\Schema\AbstractSchemaManager  $schema
     * @return \Illuminate\Support\Collection
     */
    protected function tables(ConnectionInterface $connection, AbstractSchemaManager $schema)
    {
        return collect($schema->listTables())->map(fn (Table $table, $index) => [
            'table' => $table->getName(),
            'size' => $this->getTableSize($connection, $table->getName()),
            'rows' => $this->option('counts') ? $connection->table($table->getName())->count() : null,
            'engine' => rescue(fn () => $table->getOption('engine'), null, false),
            'comment' => $table->getComment(),
        ]);
    }

    /**
     * Get information regarding the views within the database.
	 * 获取关于数据库中视图的信息
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Doctrine\DBAL\Schema\AbstractSchemaManager  $schema
     * @return \Illuminate\Support\Collection
     */
    protected function collectViews(ConnectionInterface $connection, AbstractSchemaManager $schema)
    {
        return collect($schema->listViews())
            ->reject(fn (View $view) => str($view->getName())
                ->startsWith(['pg_catalog', 'information_schema', 'spt_']))
            ->map(fn (View $view) => [
                'view' => $view->getName(),
                'rows' => $connection->table($view->getName())->count(),
            ]);
    }

    /**
     * Render the database information.
	 * 呈现数据库信息
     *
     * @param  array  $data
     * @return void
     */
    protected function display(array $data)
    {
        $this->option('json') ? $this->displayJson($data) : $this->displayForCli($data);
    }

    /**
     * Render the database information as JSON.
	 * 将数据库信息呈现为JSON
     *
     * @param  array  $data
     * @return void
     */
    protected function displayJson(array $data)
    {
        $this->output->writeln(json_encode($data));
    }

    /**
     * Render the database information formatted for the CLI.
	 * 将数据库信息格式化后呈现给CLI
     *
     * @param  array  $data
     * @return void
     */
    protected function displayForCli(array $data)
    {
        $platform = $data['platform'];
        $tables = $data['tables'];
        $views = $data['views'] ?? null;

        $this->newLine();

        $this->components->twoColumnDetail('<fg=green;options=bold>'.$platform['name'].'</>');
        $this->components->twoColumnDetail('Database', Arr::get($platform['config'], 'database'));
        $this->components->twoColumnDetail('Host', Arr::get($platform['config'], 'host'));
        $this->components->twoColumnDetail('Port', Arr::get($platform['config'], 'port'));
        $this->components->twoColumnDetail('Username', Arr::get($platform['config'], 'username'));
        $this->components->twoColumnDetail('URL', Arr::get($platform['config'], 'url'));
        $this->components->twoColumnDetail('Open Connections', $platform['open_connections']);
        $this->components->twoColumnDetail('Tables', $tables->count());

        if ($tableSizeSum = $tables->sum('size')) {
            $this->components->twoColumnDetail('Total Size', number_format($tableSizeSum / 1024 / 1024, 2).'MiB');
        }

        $this->newLine();

        if ($tables->isNotEmpty()) {
            $this->components->twoColumnDetail('<fg=green;options=bold>Table</>', 'Size (MiB)'.($this->option('counts') ? ' <fg=gray;options=bold>/</> <fg=yellow;options=bold>Rows</>' : ''));

            $tables->each(function ($table) {
                if ($tableSize = $table['size']) {
                    $tableSize = number_format($tableSize / 1024 / 1024, 2);
                }

                $this->components->twoColumnDetail(
                    $table['table'].($this->output->isVerbose() ? ' <fg=gray>'.$table['engine'].'</>' : null),
                    ($tableSize ? $tableSize : '—').($this->option('counts') ? ' <fg=gray;options=bold>/</> <fg=yellow;options=bold>'.number_format($table['rows']).'</>' : '')
                );

                if ($this->output->isVerbose()) {
                    if ($table['comment']) {
                        $this->components->bulletList([
                            $table['comment'],
                        ]);
                    }
                }
            });

            $this->newLine();
        }

        if ($views && $views->isNotEmpty()) {
            $this->components->twoColumnDetail('<fg=green;options=bold>View</>', '<fg=green;options=bold>Rows</>');

            $views->each(fn ($view) => $this->components->twoColumnDetail($view['view'], number_format($view['rows'])));

            $this->newLine();
        }
    }
}
