<?php
/**
 * Illuminate，Session会话，会话管理器
 */

namespace Illuminate\Session;

use Illuminate\Support\Manager;

class SessionManager extends Manager
{
    /**
     * Call a custom driver creator.
	 * 调用自定义驱动创建者
     *
     * @param  string  $driver
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->buildSession(parent::callCustomCreator($driver));
    }

    /**
     * Create an instance of the "null" session driver.
	 * 创建"null"会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createNullDriver()
    {
        return $this->buildSession(new NullSessionHandler);
    }

    /**
     * Create an instance of the "array" session driver.
	 * 创建"array"会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createArrayDriver()
    {
        return $this->buildSession(new ArraySessionHandler(
            $this->config->get('session.lifetime')
        ));
    }

    /**
     * Create an instance of the "cookie" session driver.
	 * 创建"cookie"会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createCookieDriver()
    {
        return $this->buildSession(new CookieSessionHandler(
            $this->container->make('cookie'), $this->config->get('session.lifetime')
        ));
    }

    /**
     * Create an instance of the file session driver.
	 * 创建文件会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createFileDriver()
    {
        return $this->createNativeDriver();
    }

    /**
     * Create an instance of the file session driver.
	 * 创建文件会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createNativeDriver()
    {
        $lifetime = $this->config->get('session.lifetime');

        return $this->buildSession(new FileSessionHandler(
            $this->container->make('files'), $this->config->get('session.files'), $lifetime
        ));
    }

    /**
     * Create an instance of the database session driver.
	 * 创建数据库会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createDatabaseDriver()
    {
        $table = $this->config->get('session.table');

        $lifetime = $this->config->get('session.lifetime');

        return $this->buildSession(new DatabaseSessionHandler(
            $this->getDatabaseConnection(), $table, $lifetime, $this->container
        ));
    }

    /**
     * Get the database connection for the database driver.
	 * 获取数据库驱动程序的数据库连接
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getDatabaseConnection()
    {
        $connection = $this->config->get('session.connection');

        return $this->container->make('db')->connection($connection);
    }

    /**
     * Create an instance of the APC session driver.
	 * 创建APC会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createApcDriver()
    {
        return $this->createCacheBased('apc');
    }

    /**
     * Create an instance of the Memcached session driver.
	 * 创建Memcached会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createMemcachedDriver()
    {
        return $this->createCacheBased('memcached');
    }

    /**
     * Create an instance of the Redis session driver.
	 * 创建一个Redis会话驱动程序实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createRedisDriver()
    {
        $handler = $this->createCacheHandler('redis');

        $handler->getCache()->getStore()->setConnection(
            $this->config->get('session.connection')
        );

        return $this->buildSession($handler);
    }

    /**
     * Create an instance of the DynamoDB session driver.
	 * 创建DynamoDB会话驱动程序的实例
     *
     * @return \Illuminate\Session\Store
     */
    protected function createDynamodbDriver()
    {
        return $this->createCacheBased('dynamodb');
    }

    /**
     * Create an instance of a cache driven driver.
	 * 创建缓存驱动程序的实例
     *
     * @param  string  $driver
     * @return \Illuminate\Session\Store
     */
    protected function createCacheBased($driver)
    {
        return $this->buildSession($this->createCacheHandler($driver));
    }

    /**
     * Create the cache based session handler instance.
	 * 创建基于缓存的会话处理程序实例
     *
     * @param  string  $driver
     * @return \Illuminate\Session\CacheBasedSessionHandler
     */
    protected function createCacheHandler($driver)
    {
        $store = $this->config->get('session.store') ?: $driver;

        return new CacheBasedSessionHandler(
            clone $this->container->make('cache')->store($store),
            $this->config->get('session.lifetime')
        );
    }

    /**
     * Build the session instance.
	 * 构建会话实例
     *
     * @param  \SessionHandlerInterface  $handler
     * @return \Illuminate\Session\Store
     */
    protected function buildSession($handler)
    {
        return $this->config->get('session.encrypt')
                ? $this->buildEncryptedSession($handler)
                : new Store($this->config->get('session.cookie'), $handler);
    }

    /**
     * Build the encrypted session instance.
	 * 构建加密的会话实例
     *
     * @param  \SessionHandlerInterface  $handler
     * @return \Illuminate\Session\EncryptedStore
     */
    protected function buildEncryptedSession($handler)
    {
        return new EncryptedStore(
            $this->config->get('session.cookie'), $handler, $this->container['encrypter']
        );
    }

    /**
     * Determine if requests for the same session should wait for each to finish before executing.
	 * 确定对同一会话的请求是否应该等待每个请求完成后再执行
     *
     * @return bool
     */
    public function shouldBlock()
    {
        return $this->config->get('session.block', false);
    }

    /**
     * Get the name of the cache store / driver that should be used to acquire session locks.
	 * 获取应该用于获取会话锁的缓存存储/驱动程序的名称
     *
     * @return string|null
     */
    public function blockDriver()
    {
        return $this->config->get('session.block_store');
    }

    /**
     * Get the session configuration.
	 * 获取会话配置
     *
     * @return array
     */
    public function getSessionConfig()
    {
        return $this->config->get('session');
    }

    /**
     * Get the default session driver name.
	 * 获取默认会话驱动程序名称
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('session.driver');
    }

    /**
     * Set the default session driver name.
	 * 设置默认的会话驱动程序名称
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->config->set('session.driver', $name);
    }
}
