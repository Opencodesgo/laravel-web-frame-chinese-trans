<?php
/**
 * Illuminate，数据库，连接解析器
 */

namespace Illuminate\Database;

class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * All of the registered connections.
	 * 所有已注册的连接
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The default connection name.
	 * 默认连接名称
     *
     * @var string
     */
    protected $default;

    /**
     * Create a new connection resolver instance.
	 * 创建一个新的连接解析器实例
     *
     * @param  array  $connections
     * @return void
     */
    public function __construct(array $connections = [])
    {
        foreach ($connections as $name => $connection) {
            $this->addConnection($name, $connection);
        }
    }

    /**
     * Get a database connection instance.
	 * 获取数据库连接实例
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        return $this->connections[$name];
    }

    /**
     * Add a connection to the resolver.
	 * 添加到解析器的连接
     *
     * @param  string  $name
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @return void
     */
    public function addConnection($name, ConnectionInterface $connection)
    {
        $this->connections[$name] = $connection;
    }

    /**
     * Check if a connection has been registered.
	 * 检查是否已经注册了连接
     *
     * @param  string  $name
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Get the default connection name.
	 * 获取默认连接名称
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
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
        $this->default = $name;
    }
}
