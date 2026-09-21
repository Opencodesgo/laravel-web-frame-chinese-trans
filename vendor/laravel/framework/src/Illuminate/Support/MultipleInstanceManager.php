<?php
/**
 * Illuminate, 支持, 多实例管理器
 */

namespace Illuminate\Support;

use Closure;
use InvalidArgumentException;
use RuntimeException;

abstract class MultipleInstanceManager
{
    /**
     * The application instance.
	 * 应用实例
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved instances.
	 * 已解析实例的数组
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The registered custom instance creators.
	 * 已注册的自定义实例创建者
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new manager instance.
	 * 创建新的实例管理器
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the default instance name.
	 * 获取默认实例名
     *
     * @return string
     */
    abstract public function getDefaultInstance();

    /**
     * Set the default instance name.
	 * 设置默认实例名
     *
     * @param  string  $name
     * @return void
     */
    abstract public function setDefaultInstance($name);

    /**
     * Get the instance specific configuration.
	 * 获取特定于实例的配置
     *
     * @param  string  $name
     * @return array
     */
    abstract public function getInstanceConfig($name);

    /**
     * Get an instance instance by name.
	 * 按名称获取实例实例
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function instance($name = null)
    {
        $name = $name ?: $this->getDefaultInstance();

        return $this->instances[$name] = $this->get($name);
    }

    /**
     * Attempt to get an instance from the local cache.
	 * 尝试从本地缓存获取实例
     *
     * @param  string  $name
     * @return mixed
     */
    protected function get($name)
    {
        return $this->instances[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given instance.
	 * 解析给定的实例
     *
     * @param  string  $name
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getInstanceConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Instance [{$name}] is not defined.");
        }

        if (! array_key_exists('driver', $config)) {
            throw new RuntimeException("Instance [{$name}] does not specify a driver.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        } else {
            $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($config);
            } else {
                throw new InvalidArgumentException("Instance driver [{$config['driver']}] is not supported.");
            }
        }
    }

    /**
     * Call a custom instance creator.
	 * 调用自定义实例创建器
     *
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Unset the given instances.
	 * 取消给定实例的设置
     *
     * @param  array|string|null  $name
     * @return $this
     */
    public function forgetInstance($name = null)
    {
        $name ??= $this->getDefaultInstance();

        foreach ((array) $name as $instanceName) {
            if (isset($this->instances[$instanceName])) {
                unset($this->instances[$instanceName]);
            }
        }

        return $this;
    }

    /**
     * Disconnect the given instance and remove from local cache.
	 * 断开给定实例的连接并从本地缓存中删除
     *
     * @param  string|null  $name
     * @return void
     */
    public function purge($name = null)
    {
        $name ??= $this->getDefaultInstance();

        unset($this->instances[$name]);
    }

    /**
     * Register a custom instance creator Closure.
	 * 注册自定义实例创建器Closure
     *
     * @param  string  $name
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($name, Closure $callback)
    {
        $this->customCreators[$name] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Dynamically call the default instance.
	 * 动态调用默认实例
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->instance()->$method(...$parameters);
    }
}
