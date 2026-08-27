<?php
/**
 * Illuminate，数据库，控制台，数据库巡检命令
 */

namespace Illuminate\Database\Console;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\QueryException;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Support\Arr;
use Illuminate\Support\Composer;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

abstract class DatabaseInspectionCommand extends Command
{
    /**
     * A map of database column types.
	 * 数据库列类型的映射
     *
     * @var array
     */
    protected $typeMappings = [
        'bit' => 'string',
        'citext' => 'string',
        'enum' => 'string',
        'geometry' => 'string',
        'geomcollection' => 'string',
        'linestring' => 'string',
        'ltree' => 'string',
        'multilinestring' => 'string',
        'multipoint' => 'string',
        'multipolygon' => 'string',
        'point' => 'string',
        'polygon' => 'string',
        'sysname' => 'string',
    ];

    /**
     * The Composer instance.
	 * Composer实例
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
	 * 创建一个新的命令实例
     *
     * @param  \Illuminate\Support\Composer|null  $composer
     * @return void
     */
    public function __construct(Composer $composer = null)
    {
        parent::__construct();

        $this->composer = $composer ?? $this->laravel->make(Composer::class);
    }

    /**
     * Register the custom Doctrine type mappings for inspection commands.
	 * 为检查命令注册自定义Doctrine类型映射
     *
     * @param  \Doctrine\DBAL\Platforms\AbstractPlatform  $platform
     * @return void
     */
    protected function registerTypeMappings(AbstractPlatform $platform)
    {
        foreach ($this->typeMappings as $type => $value) {
            $platform->registerDoctrineTypeMapping($type, $value);
        }
    }

    /**
     * Get a human-readable platform name for the given platform.
	 * 获取给定平台的人类可读的平台名称
     *
     * @param  \Doctrine\DBAL\Platforms\AbstractPlatform  $platform
     * @param  string  $database
     * @return string
     */
    protected function getPlatformName(AbstractPlatform $platform, $database)
    {
        return match (class_basename($platform)) {
            'MySQLPlatform' => 'MySQL <= 5',
            'MySQL57Platform' => 'MySQL 5.7',
            'MySQL80Platform' => 'MySQL 8',
            'PostgreSQL100Platform', 'PostgreSQLPlatform' => 'Postgres',
            'SqlitePlatform' => 'SQLite',
            'SQLServerPlatform' => 'SQL Server',
            'SQLServer2012Platform' => 'SQL Server 2012',
            default => $database,
        };
    }

    /**
     * Get the size of a table in bytes.
	 * 获取表的大小（以字节为单位）
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return int|null
     */
    protected function getTableSize(ConnectionInterface $connection, string $table)
    {
        return match (true) {
            $connection instanceof MySqlConnection => $this->getMySQLTableSize($connection, $table),
            $connection instanceof PostgresConnection => $this->getPostgresTableSize($connection, $table),
            $connection instanceof SQLiteConnection => $this->getSqliteTableSize($connection, $table),
            default => null,
        };
    }

    /**
     * Get the size of a MySQL table in bytes.
	 * 获取MySQL表的大小（以字节为单位）
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return mixed
     */
    protected function getMySQLTableSize(ConnectionInterface $connection, string $table)
    {
        $result = $connection->selectOne('SELECT (data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ? AND table_name = ?', [
            $connection->getDatabaseName(),
            $table,
        ]);

        return Arr::wrap((array) $result)['size'];
    }

    /**
     * Get the size of a Postgres table in bytes.
	 * 以字节为单位获取Postgres表的大小
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return mixed
     */
    protected function getPostgresTableSize(ConnectionInterface $connection, string $table)
    {
        $result = $connection->selectOne('SELECT pg_total_relation_size(?) AS size;', [
            $table,
        ]);

        return Arr::wrap((array) $result)['size'];
    }

    /**
     * Get the size of a SQLite table in bytes.
	 * 获取以字节为单位的SQLite表的大小
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string  $table
     * @return mixed
     */
    protected function getSqliteTableSize(ConnectionInterface $connection, string $table)
    {
        try {
            $result = $connection->selectOne('SELECT SUM(pgsize) AS size FROM dbstat WHERE name=?', [
                $table,
            ]);

            return Arr::wrap((array) $result)['size'];
        } catch (QueryException $e) {
            return null;
        }
    }

    /**
     * Get the number of open connections for a database.
	 * 获取数据库打开的连接数
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @return int|null
     */
    protected function getConnectionCount(ConnectionInterface $connection)
    {
        $result = match (true) {
            $connection instanceof MySqlConnection => $connection->selectOne('show status where variable_name = "threads_connected"'),
            $connection instanceof PostgresConnection => $connection->selectOne('select count(*) AS "Value" from pg_stat_activity'),
            $connection instanceof SqlServerConnection => $connection->selectOne('SELECT COUNT(*) Value FROM sys.dm_exec_sessions WHERE status = ?', ['running']),
            default => null,
        };

        if (! $result) {
            return null;
        }

        return Arr::wrap((array) $result)['Value'];
    }

    /**
     * Get the connection configuration details for the given connection.
	 * 获取给定连接的连接配置详细信息
     *
     * @param  string  $database
     * @return array
     */
    protected function getConfigFromDatabase($database)
    {
        $database ??= config('database.default');

        return Arr::except(config('database.connections.'.$database), ['password']);
    }

    /**
     * Ensure the dependencies for the database commands are available.
	 * 确保数据库命令的依赖项可用
     *
     * @return bool
     */
    protected function ensureDependenciesExist()
    {
        return tap(interface_exists('Doctrine\DBAL\Driver'), function ($dependenciesExist) {
            if (! $dependenciesExist && $this->components->confirm('Inspecting database information requires the Doctrine DBAL (doctrine/dbal) package. Would you like to install it?')) {
                $this->installDependencies();
            }
        });
    }

    /**
     * Install the command's dependencies.
	 * 安装命令的依赖项
     *
     * @return void
     *
     * @throws \Symfony\Component\Process\Exception\ProcessSignaledException
     */
    protected function installDependencies()
    {
        $command = collect($this->composer->findComposer())
            ->push('require doctrine/dbal')
            ->implode(' ');

        $process = Process::fromShellCommandline($command, null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->components->warn($e->getMessage());
            }
        }

        try {
            $process->run(fn ($type, $line) => $this->output->write($line));
        } catch (ProcessSignaledException $e) {
            if (extension_loaded('pcntl') && $e->getSignal() !== SIGINT) {
                throw $e;
            }
        }
    }
}
