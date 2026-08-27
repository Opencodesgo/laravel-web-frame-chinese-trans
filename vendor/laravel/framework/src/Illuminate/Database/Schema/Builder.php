<?php
/**
 * Illuminate，数据库，模式，构建者
 */

namespace Illuminate\Database\Schema;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use LogicException;

class Builder
{
    /**
     * The database connection instance.
	 * 数据库连接实例
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * The schema grammar instance.
	 * 模式语法实例
     *
     * @var \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected $grammar;

    /**
     * The Blueprint resolver callback.
	 * Blueprint解析器回调
     *
     * @var \Closure
     */
    protected $resolver;

    /**
     * The default string length for migrations.
	 * 迁移的默认字符串长度
     *
     * @var int|null
     */
    public static $defaultStringLength = 255;

    /**
     * The default relationship morph key type.
	 * 默认的关系变形键类型
     *
     * @var string
     */
    public static $defaultMorphKeyType = 'int';

    /**
     * Indicates whether Doctrine DBAL usage will be prevented if possible when dropping and renaming columns.
	 * 指示在删除列和重命名列时，是否在可能的情况下阻止Doctrine DBAL的使用。
     *
     * @var bool
     */
    public static $alwaysUsesNativeSchemaOperationsIfPossible = false;

    /**
     * Create a new database Schema manager.
	 * 创建一个新的数据库模式管理器
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }

    /**
     * Set the default string length for migrations.
	 * 设置迁移的默认字符串长度
     *
     * @param  int  $length
     * @return void
     */
    public static function defaultStringLength($length)
    {
        static::$defaultStringLength = $length;
    }

    /**
     * Set the default morph key type for migrations.
	 * 为迁移设置默认的变形键类型
     *
     * @param  string  $type
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function defaultMorphKeyType(string $type)
    {
        if (! in_array($type, ['int', 'uuid', 'ulid'])) {
            throw new InvalidArgumentException("Morph key type must be 'int', 'uuid', or 'ulid'.");
        }

        static::$defaultMorphKeyType = $type;
    }

    /**
     * Set the default morph key type for migrations to UUIDs.
	 * 为迁移到uid设置默认的变形键类型
     *
     * @return void
     */
    public static function morphUsingUuids()
    {
        return static::defaultMorphKeyType('uuid');
    }

    /**
     * Set the default morph key type for migrations to ULIDs.
	 * 为迁移到uid设置默认的变形键类型
     *
     * @return void
     */
    public static function morphUsingUlids()
    {
        return static::defaultMorphKeyType('ulid');
    }

    /**
     * Attempt to use native schema operations for dropping and renaming columns, even if Doctrine DBAL is installed.
	 * 尝试使用本地模式操作来删除和重命名列，即使安装了Doctrine DBAL。
     *
     * @param  bool  $value
     * @return void
     */
    public static function useNativeSchemaOperationsIfPossible(bool $value = true)
    {
        static::$alwaysUsesNativeSchemaOperationsIfPossible = $value;
    }

    /**
     * Create a database in the schema.
	 * 在模式中创建数据库
     *
     * @param  string  $name
     * @return bool
     *
     * @throws \LogicException
     */
    public function createDatabase($name)
    {
        throw new LogicException('This database driver does not support creating databases.');
    }

    /**
     * Drop a database from the schema if the database exists.
	 * 如果数据库存在，则从模式中删除该数据库。
     *
     * @param  string  $name
     * @return bool
     *
     * @throws \LogicException
     */
    public function dropDatabaseIfExists($name)
    {
        throw new LogicException('This database driver does not support dropping databases.');
    }

    /**
     * Determine if the given table exists.
	 * 确定给定的表是否存在
     *
     * @param  string  $table
     * @return bool
     */
    public function hasTable($table)
    {
        $table = $this->connection->getTablePrefix().$table;

        return count($this->connection->selectFromWriteConnection(
            $this->grammar->compileTableExists(), [$table]
        )) > 0;
    }

    /**
     * Determine if the given table has a given column.
	 * 确定给定表是否有给定列
     *
     * @param  string  $table
     * @param  string  $column
     * @return bool
     */
    public function hasColumn($table, $column)
    {
        return in_array(
            strtolower($column), array_map('strtolower', $this->getColumnListing($table))
        );
    }

    /**
     * Determine if the given table has given columns.
	 * 确定给定的表是否有给定的列
     *
     * @param  string  $table
     * @param  array  $columns
     * @return bool
     */
    public function hasColumns($table, array $columns)
    {
        $tableColumns = array_map('strtolower', $this->getColumnListing($table));

        foreach ($columns as $column) {
            if (! in_array(strtolower($column), $tableColumns)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute a table builder callback if the given table has a given column.
	 * 如果给定表具有给定列，则执行表构建器回调。
     *
     * @param  string  $table
     * @param  string  $column
     * @param  \Closure  $callback
     * @return void
     */
    public function whenTableHasColumn(string $table, string $column, Closure $callback)
    {
        if ($this->hasColumn($table, $column)) {
            $this->table($table, fn (Blueprint $table) => $callback($table));
        }
    }

    /**
     * Execute a table builder callback if the given table doesn't have a given column.
	 * 如果给定的表没有给定的列，则执行表构建器回调。
     *
     * @param  string  $table
     * @param  string  $column
     * @param  \Closure  $callback
     * @return void
     */
    public function whenTableDoesntHaveColumn(string $table, string $column, Closure $callback)
    {
        if (! $this->hasColumn($table, $column)) {
            $this->table($table, fn (Blueprint $table) => $callback($table));
        }
    }

    /**
     * Get the data type for the given column name.
	 * 获取给定列名的数据类型
     *
     * @param  string  $table
     * @param  string  $column
     * @return string
     */
    public function getColumnType($table, $column)
    {
        $table = $this->connection->getTablePrefix().$table;

        return $this->connection->getDoctrineColumn($table, $column)->getType()->getName();
    }

    /**
     * Get the column listing for a given table.
	 * 获取给定表的列清单
     *
     * @param  string  $table
     * @return array
     */
    public function getColumnListing($table)
    {
        $results = $this->connection->selectFromWriteConnection($this->grammar->compileColumnListing(
            $this->connection->getTablePrefix().$table
        ));

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Modify a table on the schema.
	 * 修改模式上的表
     *
     * @param  string  $table
     * @param  \Closure  $callback
     * @return void
     */
    public function table($table, Closure $callback)
    {
        $this->build($this->createBlueprint($table, $callback));
    }

    /**
     * Create a new table on the schema.
	 * 在模式上创建一个新表
     *
     * @param  string  $table
     * @param  \Closure  $callback
     * @return void
     */
    public function create($table, Closure $callback)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback) {
            $blueprint->create();

            $callback($blueprint);
        }));
    }

    /**
     * Drop a table from the schema.
	 * 从模式中删除一个表
     *
     * @param  string  $table
     * @return void
     */
    public function drop($table)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->drop();
        }));
    }

    /**
     * Drop a table from the schema if it exists.
	 * 从模式中删除存在的表
     *
     * @param  string  $table
     * @return void
     */
    public function dropIfExists($table)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->dropIfExists();
        }));
    }

    /**
     * Drop columns from a table schema.
	 * 从表模式中删除列
     *
     * @param  string  $table
     * @param  string|array  $columns
     * @return void
     */
    public function dropColumns($table, $columns)
    {
        $this->table($table, function (Blueprint $blueprint) use ($columns) {
            $blueprint->dropColumn($columns);
        });
    }

    /**
     * Drop all tables from the database.
	 * 从数据库中删除所有表
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function dropAllTables()
    {
        throw new LogicException('This database driver does not support dropping all tables.');
    }

    /**
     * Drop all views from the database.
	 * 从数据库中删除所有视图
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function dropAllViews()
    {
        throw new LogicException('This database driver does not support dropping all views.');
    }

    /**
     * Drop all types from the database.
	 * 从数据库中删除所有类型
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function dropAllTypes()
    {
        throw new LogicException('This database driver does not support dropping all types.');
    }

    /**
     * Get all of the table names for the database.
	 * 获取数据库的所有表
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function getAllTables()
    {
        throw new LogicException('This database driver does not support getting all tables.');
    }

    /**
     * Rename a table on the schema.
	 * 重命名模式上的表
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    public function rename($from, $to)
    {
        $this->build(tap($this->createBlueprint($from), function ($blueprint) use ($to) {
            $blueprint->rename($to);
        }));
    }

    /**
     * Enable foreign key constraints.
	 * 启用外键约束
     *
     * @return bool
     */
    public function enableForeignKeyConstraints()
    {
        return $this->connection->statement(
            $this->grammar->compileEnableForeignKeyConstraints()
        );
    }

    /**
     * Disable foreign key constraints.
	 * 禁用外键约束
     *
     * @return bool
     */
    public function disableForeignKeyConstraints()
    {
        return $this->connection->statement(
            $this->grammar->compileDisableForeignKeyConstraints()
        );
    }

    /**
     * Disable foreign key constraints during the execution of a callback.
	 * 在回调执行期间禁用外键约束
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    public function withoutForeignKeyConstraints(Closure $callback)
    {
        $this->disableForeignKeyConstraints();

        $result = $callback();

        $this->enableForeignKeyConstraints();

        return $result;
    }

    /**
     * Execute the blueprint to build / modify the table.
	 * 执行蓝图来构建/修改表
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @return void
     */
    protected function build(Blueprint $blueprint)
    {
        $blueprint->build($this->connection, $this->grammar);
    }

    /**
     * Create a new command set with a Closure.
	 * 使用Closure创建一个新的命令集
     *
     * @param  string  $table
     * @param  \Closure|null  $callback
     * @return \Illuminate\Database\Schema\Blueprint
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        $prefix = $this->connection->getConfig('prefix_indexes')
                    ? $this->connection->getConfig('prefix')
                    : '';

        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback, $prefix);
        }

        return Container::getInstance()->make(Blueprint::class, compact('table', 'callback', 'prefix'));
    }

    /**
     * Get the database connection instance.
	 * 获取数据库连接实例
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set the database connection instance.
	 * 设置数据库连接实例
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return $this
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the Schema Blueprint resolver callback.
	 * 设置架构蓝图解析器回调
     *
     * @param  \Closure  $resolver
     * @return void
     */
    public function blueprintResolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }
}
