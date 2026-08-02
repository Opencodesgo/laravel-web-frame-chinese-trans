<?php
/**
 * Illuminate，数据库，架构，Postgres 模式状态
 */

namespace Illuminate\Database\Schema;

use Illuminate\Database\Connection;
use Illuminate\Support\Str;

class PostgresSchemaState extends SchemaState
{
    /**
     * Dump the database's schema into a file.
	 * 将数据库的模式转储到文件中
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  string  $path
     * @return void
     */
    public function dump(Connection $connection, $path)
    {
        $excludedTables = collect($connection->getSchemaBuilder()->getAllTables())
                        ->map->tablename
                        ->reject(function ($table) {
                            return $table === $this->migrationTable;
                        })->map(function ($table) {
                            return '--exclude-table-data="*.'.$table.'"';
                        })->implode(' ');

        $this->makeProcess(
            $this->baseDumpCommand().' --file="${:LARAVEL_LOAD_PATH}" '.$excludedTables
        )->mustRun($this->output, array_merge($this->baseVariables($this->connection->getConfig()), [
            'LARAVEL_LOAD_PATH' => $path,
        ]));
    }

    /**
     * Load the given schema file into the database.
	 * 将给定的模式文件加载到数据库中
     *
     * @param  string  $path
     * @return void
     */
    public function load($path)
    {
        $command = 'pg_restore --no-owner --no-acl --clean --if-exists --host="${:LARAVEL_LOAD_HOST}" --port="${:LARAVEL_LOAD_PORT}" --username="${:LARAVEL_LOAD_USER}" --dbname="${:LARAVEL_LOAD_DATABASE}" "${:LARAVEL_LOAD_PATH}"';

        if (Str::endsWith($path, '.sql')) {
            $command = 'psql --file="${:LARAVEL_LOAD_PATH}" --host="${:LARAVEL_LOAD_HOST}" --port="${:LARAVEL_LOAD_PORT}" --username="${:LARAVEL_LOAD_USER}" --dbname="${:LARAVEL_LOAD_DATABASE}"';
        }

        $process = $this->makeProcess($command);

        $process->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [
            'LARAVEL_LOAD_PATH' => $path,
        ]));
    }

    /**
     * Get the base dump command arguments for PostgreSQL as a string.
	 * 以字符串形式获取PostgreSQL的基本转储命令参数
     *
     * @return string
     */
    protected function baseDumpCommand()
    {
        return 'pg_dump --no-owner --no-acl -Fc --host="${:LARAVEL_LOAD_HOST}" --port="${:LARAVEL_LOAD_PORT}" --username="${:LARAVEL_LOAD_USER}" --dbname="${:LARAVEL_LOAD_DATABASE}"';
    }

    /**
     * Get the base variables for a dump / load command.
	 * 获取转储/加载命令的基本变量
     *
     * @param  array  $config
     * @return array
     */
    protected function baseVariables(array $config)
    {
        $config['host'] = $config['host'] ?? '';

        return [
            'LARAVEL_LOAD_HOST' => is_array($config['host']) ? $config['host'][0] : $config['host'],
            'LARAVEL_LOAD_PORT' => $config['port'],
            'LARAVEL_LOAD_USER' => $config['username'],
            'PGPASSWORD' => $config['password'],
            'LARAVEL_LOAD_DATABASE' => $config['database'],
        ];
    }
}
