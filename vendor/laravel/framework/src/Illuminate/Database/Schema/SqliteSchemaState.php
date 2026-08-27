<?php
/**
 * Illuminate，数据库，模式，SQLite 模式状态
 */

namespace Illuminate\Database\Schema;

use Illuminate\Database\Connection;

class SqliteSchemaState extends SchemaState
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
        with($process = $this->makeProcess(
            $this->baseCommand().' .schema'
        ))->setTimeout(null)->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [
            //
        ]));

        $migrations = collect(preg_split("/\r\n|\n|\r/", $process->getOutput()))->filter(function ($line) {
            return stripos($line, 'sqlite_sequence') === false &&
                   strlen($line) > 0;
        })->all();

        $this->files->put($path, implode(PHP_EOL, $migrations).PHP_EOL);

        $this->appendMigrationData($path);
    }

    /**
     * Append the migration data to the schema dump.
	 * 将迁移数据附加到模式转储
     *
     * @param  string  $path
     * @return void
     */
    protected function appendMigrationData(string $path)
    {
        with($process = $this->makeProcess(
            $this->baseCommand().' ".dump \''.$this->migrationTable.'\'"'
        ))->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [
            //
        ]));

        $migrations = collect(preg_split("/\r\n|\n|\r/", $process->getOutput()))->filter(function ($line) {
            return preg_match('/^\s*(--|INSERT\s)/iu', $line) === 1 &&
                   strlen($line) > 0;
        })->all();

        $this->files->append($path, implode(PHP_EOL, $migrations).PHP_EOL);
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
        if ($this->connection->getDatabaseName() === ':memory:') {
            $this->connection->getPdo()->exec($this->files->get($path));

            return;
        }

        $process = $this->makeProcess($this->baseCommand().' < "${:LARAVEL_LOAD_PATH}"');

        $process->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [
            'LARAVEL_LOAD_PATH' => $path,
        ]));
    }

    /**
     * Get the base sqlite command arguments as a string.
	 * 以字符串的形式获取基本sqlite命令参数
     *
     * @return string
     */
    protected function baseCommand()
    {
        return 'sqlite3 "${:LARAVEL_LOAD_DATABASE}"';
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
        return [
            'LARAVEL_LOAD_DATABASE' => $config['database'],
        ];
    }
}
