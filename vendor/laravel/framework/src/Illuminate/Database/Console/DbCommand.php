<?php
/**
 * Illuminate，数据库，控制台，Db命令
 */

namespace Illuminate\Database\Console;

use Illuminate\Console\Command;
use Illuminate\Support\ConfigurationUrlParser;
use Symfony\Component\Process\Process;
use UnexpectedValueException;

class DbCommand extends Command
{
    /**
     * The name and signature of the console command.
	 * console命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'db {connection? : The database connection that should be used}
               {--read : Connect to the read connection}
               {--write : Connect to the write connection}';

    /**
     * The console command description.
	 * console命令描述
     *
     * @var string
     */
    protected $description = 'Start a new database CLI session';

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->getConnection();

        if (! isset($connection['host']) && $connection['driver'] !== 'sqlite') {
            $this->components->error('No host specified for this database connection.');
            $this->line('  Use the <options=bold>[--read]</> and <options=bold>[--write]</> options to specify a read or write connection.');
            $this->newLine();

            return Command::FAILURE;
        }

        (new Process(
            array_merge([$this->getCommand($connection)], $this->commandArguments($connection)),
            null,
            $this->commandEnvironment($connection)
        ))->setTimeout(null)->setTty(true)->mustRun(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return 0;
    }

    /**
     * Get the database connection configuration.
	 * 获取数据库连接配置
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    public function getConnection()
    {
        $connection = $this->laravel['config']['database.connections.'.
            (($db = $this->argument('connection')) ?? $this->laravel['config']['database.default'])
        ];

        if (empty($connection)) {
            throw new UnexpectedValueException("Invalid database connection [{$db}].");
        }

        if (! empty($connection['url'])) {
            $connection = (new ConfigurationUrlParser)->parseConfiguration($connection);
        }

        if ($this->option('read')) {
            if (is_array($connection['read']['host'])) {
                $connection['read']['host'] = $connection['read']['host'][0];
            }

            $connection = array_merge($connection, $connection['read']);
        } elseif ($this->option('write')) {
            if (is_array($connection['write']['host'])) {
                $connection['write']['host'] = $connection['write']['host'][0];
            }

            $connection = array_merge($connection, $connection['write']);
        }

        return $connection;
    }

    /**
     * Get the arguments for the database client command.
	 * 获取数据库客户机命令的参数
     *
     * @param  array  $connection
     * @return array
     */
    public function commandArguments(array $connection)
    {
        $driver = ucfirst($connection['driver']);

        return $this->{"get{$driver}Arguments"}($connection);
    }

    /**
     * Get the environment variables for the database client command.
	 * 获取数据库客户机命令的环境变量
     *
     * @param  array  $connection
     * @return array|null
     */
    public function commandEnvironment(array $connection)
    {
        $driver = ucfirst($connection['driver']);

        if (method_exists($this, "get{$driver}Environment")) {
            return $this->{"get{$driver}Environment"}($connection);
        }

        return null;
    }

    /**
     * Get the database client command to run.
	 * 获取要运行的数据库客户机命令
     *
     * @param  array  $connection
     * @return string
     */
    public function getCommand(array $connection)
    {
        return [
            'mysql' => 'mysql',
            'pgsql' => 'psql',
            'sqlite' => 'sqlite3',
            'sqlsrv' => 'sqlcmd',
        ][$connection['driver']];
    }

    /**
     * Get the arguments for the MySQL CLI.
	 * 获取MySQL CLI的参数
     *
     * @param  array  $connection
     * @return array
     */
    protected function getMysqlArguments(array $connection)
    {
        return array_merge([
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
        ], $this->getOptionalArguments([
            'password' => '--password='.$connection['password'],
            'unix_socket' => '--socket='.($connection['unix_socket'] ?? ''),
            'charset' => '--default-character-set='.($connection['charset'] ?? ''),
        ], $connection), [$connection['database']]);
    }

    /**
     * Get the arguments for the Postgres CLI.
	 * 获取Postgres CLI的参数
     *
     * @param  array  $connection
     * @return array
     */
    protected function getPgsqlArguments(array $connection)
    {
        return [$connection['database']];
    }

    /**
     * Get the arguments for the SQLite CLI.
	 * 获取SQLite CLI的参数
     *
     * @param  array  $connection
     * @return array
     */
    protected function getSqliteArguments(array $connection)
    {
        return [$connection['database']];
    }

    /**
     * Get the arguments for the SQL Server CLI.
	 * 获取SQL Server CLI的参数
     *
     * @param  array  $connection
     * @return array
     */
    protected function getSqlsrvArguments(array $connection)
    {
        return array_merge(...$this->getOptionalArguments([
            'database' => ['-d', $connection['database']],
            'username' => ['-U', $connection['username']],
            'password' => ['-P', $connection['password']],
            'host' => ['-S', 'tcp:'.$connection['host']
                        .($connection['port'] ? ','.$connection['port'] : ''), ],
        ], $connection));
    }

    /**
     * Get the environment variables for the Postgres CLI.
	 * 获取Postgres CLI的环境变量
     *
     * @param  array  $connection
     * @return array|null
     */
    protected function getPgsqlEnvironment(array $connection)
    {
        return array_merge(...$this->getOptionalArguments([
            'username' => ['PGUSER' => $connection['username']],
            'host' => ['PGHOST' => $connection['host']],
            'port' => ['PGPORT' => $connection['port']],
            'password' => ['PGPASSWORD' => $connection['password']],
        ], $connection));
    }

    /**
     * Get the optional arguments based on the connection configuration.
	 * 根据连接配置获取可选参数
     *
     * @param  array  $args
     * @param  array  $connection
     * @return array
     */
    protected function getOptionalArguments(array $args, array $connection)
    {
        return array_values(array_filter($args, function ($key) use ($connection) {
            return ! empty($connection[$key]);
        }, ARRAY_FILTER_USE_KEY));
    }
}
