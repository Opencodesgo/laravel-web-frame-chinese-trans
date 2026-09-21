<?php
/**
 * Illuminate，数据库，模式，模式状态
 */

namespace Illuminate\Database\Schema;

use Illuminate\Database\Connection;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class SchemaState
{
    /**
     * The connection instance.
	 * 连接实例
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * The filesystem instance.
	 * 文件系统实例
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The name of the application's migration table.
	 * 应用程序迁移表的名称
     *
     * @var string
     */
    protected $migrationTable = 'migrations';

    /**
     * The process factory callback.
	 * 流程工厂回调
     *
     * @var callable
     */
    protected $processFactory;

    /**
     * The output callable instance.
	 * 输出可调用实例
     *
     * @var callable
     */
    protected $output;

    /**
     * Create a new dumper instance.
	 * 创建一个新的转储程序实例
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  \Illuminate\Filesystem\Filesystem|null  $files
     * @param  callable|null  $processFactory
     * @return void
     */
    public function __construct(Connection $connection, Filesystem $files = null, callable $processFactory = null)
    {
        $this->connection = $connection;

        $this->files = $files ?: new Filesystem;

        $this->processFactory = $processFactory ?: function (...$arguments) {
            return Process::fromShellCommandline(...$arguments)->setTimeout(null);
        };

        $this->handleOutputUsing(function () {
            //
        });
    }

    /**
     * Dump the database's schema into a file.
	 * 将数据库的模式转储到文件中
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  string  $path
     * @return void
     */
    abstract public function dump(Connection $connection, $path);

    /**
     * Load the given schema file into the database.
	 * 将给定的模式文件加载到数据库中
     *
     * @param  string  $path
     * @return void
     */
    abstract public function load($path);

    /**
     * Create a new process instance.
	 * 创建一个新的流程实例
     *
     * @param  mixed  ...$arguments
     * @return \Symfony\Component\Process\Process
     */
    public function makeProcess(...$arguments)
    {
        return call_user_func($this->processFactory, ...$arguments);
    }

    /**
     * Specify the name of the application's migration table.
	 * 指定应用程序迁移表的名称
     *
     * @param  string  $table
     * @return $this
     */
    public function withMigrationTable(string $table)
    {
        $this->migrationTable = $table;

        return $this;
    }

    /**
     * Specify the callback that should be used to handle process output.
	 * 指定应用于处理流程输出的回调
     *
     * @param  callable  $output
     * @return $this
     */
    public function handleOutputUsing(callable $output)
    {
        $this->output = $output;

        return $this;
    }
}
