<?php
/**
 * Illuminate，支持，门面，模式
 */

namespace Illuminate\Support\Facades;

/**
 * @method static void defaultStringLength(int $length)
 * @method static void defaultMorphKeyType(string $type)
 * @method static void morphUsingUuids()
 * @method static void morphUsingUlids()
 * @method static void useNativeSchemaOperationsIfPossible(bool $value = true)
 * @method static bool createDatabase(string $name)
 * @method static bool dropDatabaseIfExists(string $name)
 * @method static bool hasTable(string $table)
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static void whenTableHasColumn(string $table, string $column, \Closure $callback)
 * @method static void whenTableDoesntHaveColumn(string $table, string $column, \Closure $callback)
 * @method static string getColumnType(string $table, string $column)
 * @method static array getColumnListing(string $table)
 * @method static void table(string $table, \Closure $callback)
 * @method static void create(string $table, \Closure $callback)
 * @method static void drop(string $table)
 * @method static void dropIfExists(string $table)
 * @method static void dropColumns(string $table, string|array $columns)
 * @method static void dropAllTables()
 * @method static void dropAllViews()
 * @method static void dropAllTypes()
 * @method static void getAllTables()
 * @method static void rename(string $from, string $to)
 * @method static bool enableForeignKeyConstraints()
 * @method static bool disableForeignKeyConstraints()
 * @method static mixed withoutForeignKeyConstraints(\Closure $callback)
 * @method static \Illuminate\Database\Connection getConnection()
 * @method static \Illuminate\Database\Schema\Builder setConnection(\Illuminate\Database\Connection $connection)
 * @method static void blueprintResolver(\Closure $resolver)
 *
 * @see \Illuminate\Database\Schema\Builder
 */
class Schema extends Facade
{
    /**
     * Indicates if the resolved facade should be cached.
	 * 指示是否应缓存已解析的facade
     *
     * @var bool
     */
    protected static $cached = false;

    /**
     * Get a schema builder instance for a connection.
	 * 获取连接的架构构建器实例
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function connection($name)
    {
        return static::$app['db']->connection($name)->getSchemaBuilder();
    }

    /**
     * Get the registered name of the component.
	 * 获取组件的注册名称
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db.schema';
    }
}
