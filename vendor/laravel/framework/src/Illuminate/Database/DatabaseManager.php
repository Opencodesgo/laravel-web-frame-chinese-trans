<?php
/**
 * Illuminate，数据库，数据库管理器
 */

namespace Illuminate\Database;

use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Arr;
use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * @mixin \Illuminate\Database\Connection
 */
class DatabaseManager implements ConnectionResolverInterface
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The application instance.
	 * 应用实例
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The database connection factory instance.
	 * 数据库连接工厂实例
     *
     * @var \Illuminate\Database\Connectors\ConnectionFactory
     */
    protected $factory;

    /**
     * The active connection instances.
	 * 活动连接实例
     *
     * @var array<string, \Illuminate\Database\Connection>
     */
    protected $connections = [];

    /**
     * The custom connection resolvers.
	 * 自定义连接解析器
     *
     * @var array<string, callable>
     */
    protected $extensions = [];

    /**
     * The callback to be executed to reconnect to a database.
	 * 为重新连接数据库而执行的回调
     *
     * @var callable
     */
    protected $reconnector;

    /**
     * The custom Doctrine column types.
	 * 自定义Doctrine列类型
     *
     * @var array<string, array>
     */
    protected $doctrineTypes = [];

    /**
     * Create a new database manager instance.
	 * 创建一个新的数据库管理器实例
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Database\Connectors\ConnectionFactory  $factory
     * @return void
     */
    public function __construct($app, ConnectionFactory $factory)
    {
        $this->app = $app;
        $this->factory = $factory;

        $this->reconnector = function ($connection) {
            $this->reconnect($connection->getNameWithReadWriteType());
        };
    }

    /**
     * Get a database connection instance.
	 * 获取数据库连接实例
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\Connection
     */
    public function connection($name = null)
    {
        [$database, $type] = $this->parseConnectionName($name);

        $name = $name ?: $database;

        // If we haven't created this connection, we'll create it based on the config
        // provided in the application. Once we've created the connections we will
        // set the "fetch mode" for PDO which determines the query return types.
		// 如果我们没有建立这种联系，
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->configure(
                $this->makeConnection($database), $type
            );

            if ($this->app->bound('events')) {
                $this->app['events']->dispatch(
                    new ConnectionEstablished($this->connections[$name])
                );
            }
        }

        return $this->connections[$name];
    }

    /**
     * Parse the connection into an array of the name and read / write type.
	 * 将连接解析为名称和读/写类型的数组
     *
     * @param  string  $name
     * @return array
     */
    protected function parseConnectionName($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        return Str::endsWith($name, ['::read', '::write'])
                            ? explode('::', $name, 2) : [$name, null];
    }

    /**
     * Make the database connection instance.
	 * 创建数据库连接实例
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        // First we will check by the connection name to see if an extension has been
        // registered specifically for that connection. If it has we will call the
        // Closure and pass it the config allowing it to resolve the connection.
		// 首先，我们将检查连接名称，看看是否有扩展。
        if (isset($this->extensions[$name])) {
            return call_user_func($this->extensions[$name], $config, $name);
        }

        // Next we will check to see if an extension has been registered for a driver
        // and will call the Closure if so, which allows us to have a more generic
        // resolver for the drivers themselves which applies to all connections.
		// 接下来，我们将检查是否已为驱动程序注册了扩展。
        if (isset($this->extensions[$driver = $config['driver']])) {
            return call_user_func($this->extensions[$driver], $config, $name);
        }

        return $this->factory->make($config, $name);
    }

    /**
     * Get the configuration for a connection.
	 * 获取连接的配置
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function configuration($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        // To get the database connection configuration, we will just pull each of the
        // connection configurations and get the configurations for the given name.
        // If the configuration doesn't exist, we'll throw an exception and bail.
		// 要获得数据库连接配置，我们只需拉出每个。
        $connections = $this->app['config']['database.connections'];

        if (is_null($config = Arr::get($connections, $name))) {
            throw new InvalidArgumentException("Database connection [{$name}] not configured.");
        }

        return (new ConfigurationUrlParser)
                    ->parseConfiguration($config);
    }

    /**
     * Prepare the database connection instance.
	 * 准备数据库连接实例
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  string  $type
     * @return \Illuminate\Database\Connection
     */
    protected function configure(Connection $connection, $type)
    {
        $connection = $this->setPdoForType($connection, $type)->setReadWriteType($type);

        // First we'll set the fetch mode and a few other dependencies of the database
        // connection. This method basically just configures and prepares it to get
        // used by the application. Once we're finished we'll return it back out.
		// 首先，我们将设置获取模式和数据库的其他一些依赖项。
        if ($this->app->bound('events')) {
            $connection->setEventDispatcher($this->app['events']);
        }

        if ($this->app->bound('db.transactions')) {
            $connection->setTransactionManager($this->app['db.transactions']);
        }

        // Here we'll set a reconnector callback. This reconnector can be any callable
        // so we will set a Closure to reconnect from this manager with the name of
        // the connection, which will allow us to reconnect from the connections.
        $connection->setReconnector($this->reconnector);

        $this->registerConfiguredDoctrineTypes($connection);

        return $connection;
    }

    /**
     * Prepare the read / write mode for database connection instance.
	 * 为数据库连接实例准备读写模式
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  string|null  $type
     * @return \Illuminate\Database\Connection
     */
    protected function setPdoForType(Connection $connection, $type = null)
    {
        if ($type === 'read') {
            $connection->setPdo($connection->getReadPdo());
        } elseif ($type === 'write') {
            $connection->setReadPdo($connection->getPdo());
        }

        return $connection;
    }

    /**
     * Register custom Doctrine types with the connection.
	 * 用连接注册自定义Doctrine类型
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return void
     */
    protected function registerConfiguredDoctrineTypes(Connection $connection): void
    {
        foreach ($this->app['config']->get('database.dbal.types', []) as $name => $class) {
            $this->registerDoctrineType($class, $name, $name);
        }

        foreach ($this->doctrineTypes as $name => [$type, $class]) {
            $connection->registerDoctrineType($class, $name, $type);
        }
    }

    /**
     * Register a custom Doctrine type.
	 * 注册一个自定义Doctrine类型
     *
     * @param  string  $class
     * @param  string  $name
     * @param  string  $type
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \RuntimeException
     */
    public function registerDoctrineType(string $class, string $name, string $type): void
    {
        if (! class_exists('Doctrine\DBAL\Connection')) {
            throw new RuntimeException(
                'Registering a custom Doctrine type requires Doctrine DBAL (doctrine/dbal).'
            );
        }

        if (! Type::hasType($name)) {
            Type::addType($name, $class);
        }

        $this->doctrineTypes[$name] = [$type, $class];
    }

    /**
     * Disconnect from the given database and remove from local cache.
	 * 断开与给定数据库的连接，并从本地缓存中删除。
     *
     * @param  string|null  $name
     * @return void
     */
    public function purge($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();

        $this->disconnect($name);

        unset($this->connections[$name]);
    }

    /**
     * Disconnect from the given database.
	 * 断开与给定数据库的连接
     *
     * @param  string|null  $name
     * @return void
     */
    public function disconnect($name = null)
    {
        if (isset($this->connections[$name = $name ?: $this->getDefaultConnection()])) {
            $this->connections[$name]->disconnect();
        }
    }

    /**
     * Reconnect to the given database.
	 * 重新连接到给定的数据库
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\Connection
     */
    public function reconnect($name = null)
    {
        $this->disconnect($name = $name ?: $this->getDefaultConnection());

        if (! isset($this->connections[$name])) {
            return $this->connection($name);
        }

        return $this->refreshPdoConnections($name);
    }

    /**
     * Set the default database connection for the callback execution.
	 * 为回调执行设置默认数据库连接
     *
     * @param  string  $name
     * @param  callable  $callback
     * @return mixed
     */
    public function usingConnection($name, callable $callback)
    {
        $previousName = $this->getDefaultConnection();

        $this->setDefaultConnection($name);

        return tap($callback(), function () use ($previousName) {
            $this->setDefaultConnection($previousName);
        });
    }

    /**
     * Refresh the PDO connections on a given connection.
	 * 刷新给定连接上的PDO连接
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    protected function refreshPdoConnections($name)
    {
        [$database, $type] = $this->parseConnectionName($name);

        $fresh = $this->configure(
            $this->makeConnection($database), $type
        );

        return $this->connections[$name]
                    ->setPdo($fresh->getRawPdo())
                    ->setReadPdo($fresh->getRawReadPdo());
    }

    /**
     * Get the default connection name.
	 * 获取默认连接名称
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->app['config']['database.default'];
    }

    /**
     * Set the default connection name.
	 * 设置默认连接名称
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->app['config']['database.default'] = $name;
    }

    /**
     * Get all of the support drivers.
	 * 找所有的支持司机
     *
     * @return string[]
     */
    public function supportedDrivers()
    {
        return ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];
    }

    /**
     * Get all of the drivers that are actually available.
	 * 获取所有可用的驱动程序
     *
     * @return string[]
     */
    public function availableDrivers()
    {
        return array_intersect(
            $this->supportedDrivers(),
            str_replace('dblib', 'sqlsrv', PDO::getAvailableDrivers())
        );
    }

    /**
     * Register an extension connection resolver.
	 * 注册扩展连接解析器
     *
     * @param  string  $name
     * @param  callable  $resolver
     * @return void
     */
    public function extend($name, callable $resolver)
    {
        $this->extensions[$name] = $resolver;
    }

    /**
     * Remove an extension connection resolver.
	 * 删除扩展连接解析器
     *
     * @param  string  $name
     * @return void
     */
    public function forgetExtension($name)
    {
        unset($this->extensions[$name]);
    }

    /**
     * Return all of the created connections.
	 * 返回所有创建的连接
     *
     * @return array<string, \Illuminate\Database\Connection>
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Set the database reconnector callback.
	 * 设置数据库重接器回调
     *
     * @param  callable  $reconnector
     * @return void
     */
    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;
    }

    /**
     * Set the application instance used by the manager.
	 * 设置管理员使用的应用实例
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return $this
     */
    public function setApplication($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Dynamically pass methods to the default connection.
	 * 动态地将方法传递给默认连接
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->connection()->$method(...$parameters);
    }
}
