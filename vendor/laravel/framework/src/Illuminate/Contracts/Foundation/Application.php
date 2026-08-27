<?php
/**
 * Illuminate，契约，基础，应用
 */

namespace Illuminate\Contracts\Foundation;

use Illuminate\Contracts\Container\Container;

interface Application extends Container
{
    /**
     * Get the version number of the application.
	 * 得到应用的版本号
     *
     * @return string
     */
    public function version();

    /**
     * Get the base path of the Laravel installation.
	 * 获取Laravel安装的基本路径
     *
     * @param  string  $path
     * @return string
     */
    public function basePath($path = '');

    /**
     * Get the path to the bootstrap directory.
	 * 获取引导目录的路径
     *
     * @param  string  $path
     * @return string
     */
    public function bootstrapPath($path = '');

    /**
     * Get the path to the application configuration files.
	 * 获取应用程序配置文件的路径
     *
     * @param  string  $path
     * @return string
     */
    public function configPath($path = '');

    /**
     * Get the path to the database directory.
	 * 获取数据库目录的路径
     *
     * @param  string  $path
     * @return string
     */
    public function databasePath($path = '');

    /**
     * Get the path to the resources directory.
	 * 获取资源目录的路径
     *
     * @param  string  $path
     * @return string
     */
    public function resourcePath($path = '');

    /**
     * Get the path to the storage directory.
	 * 获取存储目录的路径
     *
     * @param  string  $path
     * @return string
     */
    public function storagePath($path = '');

    /**
     * Get or check the current application environment.
	 * 获取或检查当前应用程序环境
     *
     * @param  string|array  ...$environments
     * @return string|bool
     */
    public function environment(...$environments);

    /**
     * Determine if the application is running in the console.
	 * 确定应用程序是否在控制台中运行
     *
     * @return bool
     */
    public function runningInConsole();

    /**
     * Determine if the application is running unit tests.
	 * 确定应用程序是否正在运行单元测试
     *
     * @return bool
     */
    public function runningUnitTests();

    /**
     * Get an instance of the maintenance mode manager implementation.
	 * 获取维护模式管理器实现的实例
     *
     * @return \Illuminate\Contracts\Foundation\MaintenanceMode
     */
    public function maintenanceMode();

    /**
     * Determine if the application is currently down for maintenance.
	 * 确定应用程序当前是否关闭以进行维护
     *
     * @return bool
     */
    public function isDownForMaintenance();

    /**
     * Register all of the configured providers.
	 * 注册所有已配置的提供程序
     *
     * @return void
     */
    public function registerConfiguredProviders();

    /**
     * Register a service provider with the application.
	 * 向应用程序注册一个服务提供者
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool  $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false);

    /**
     * Register a deferred provider and service.
	 * 注册一个延迟的提供者和服务
     *
     * @param  string  $provider
     * @param  string|null  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null);

    /**
     * Resolve a service provider instance from the class name.
	 * 从类名解析服务提供者实例
     *
     * @param  string  $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function resolveProvider($provider);

    /**
     * Boot the application's service providers.
	 * 引导应用程序的服务提供者
     *
     * @return void
     */
    public function boot();

    /**
     * Register a new boot listener.
	 * 注册一个新的引导侦听器
     *
     * @param  callable  $callback
     * @return void
     */
    public function booting($callback);

    /**
     * Register a new "booted" listener.
	 * 注册一个新的“已启动”侦听器
     *
     * @param  callable  $callback
     * @return void
     */
    public function booted($callback);

    /**
     * Run the given array of bootstrap classes.
	 * 运行给定的引导类数组
     *
     * @param  array  $bootstrappers
     * @return void
     */
    public function bootstrapWith(array $bootstrappers);

    /**
     * Get the current application locale.
	 * 获取当前应用程序区域设置
     *
     * @return string
     */
    public function getLocale();

    /**
     * Get the application namespace.
	 * 获取应用程序名称空间
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace();

    /**
     * Get the registered service provider instances if any exist.
	 * 获取注册的服务提供者实例（如果存在的话）
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return array
     */
    public function getProviders($provider);

    /**
     * Determine if the application has been bootstrapped before.
	 * 确定应用程序之前是否被引导过
     *
     * @return bool
     */
    public function hasBeenBootstrapped();

    /**
     * Load and boot all of the remaining deferred providers.
	 * 加载并引导所有剩余的延迟提供程序
     *
     * @return void
     */
    public function loadDeferredProviders();

    /**
     * Set the current application locale.
	 * 设置当前应用程序区域设置
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale);

    /**
     * Determine if middleware has been disabled for the application.
	 * 确定是否为应用程序禁用了中间件
     *
     * @return bool
     */
    public function shouldSkipMiddleware();

    /**
     * Register a terminating callback with the application.
	 * 向应用程序注册一个终止回调
     *
     * @param  callable|string  $callback
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function terminating($callback);

    /**
     * Terminate the application.
	 * 按确定终止应用程序
     *
     * @return void
     */
    public function terminate();
}
