<?php
/**
 * Illuminate，认证，创建用户提供者
 */

namespace Illuminate\Auth;

use InvalidArgumentException;

trait CreatesUserProviders
{
    /**
     * The registered custom provider creators.
	 * 已注册自定义提供程序创建者
     *
     * @var array
     */
    protected $customProviderCreators = [];

    /**
     * Create the user provider implementation for the driver.
	 * 为驱动程序创建用户提供程序实现
     *
     * @param  string|null  $provider
     * @return \Illuminate\Contracts\Auth\UserProvider|null
     *
     * @throws \InvalidArgumentException
     */
    public function createUserProvider($provider = null)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return;
        }

        if (isset($this->customProviderCreators[$driver = ($config['driver'] ?? null)])) {
            return call_user_func(
                $this->customProviderCreators[$driver], $this->app, $config
            );
        }

        return match ($driver) {
            'database' => $this->createDatabaseProvider($config),
            'eloquent' => $this->createEloquentProvider($config),
            default => throw new InvalidArgumentException(
                "Authentication user provider [{$driver}] is not defined."
            ),
        };
    }

    /**
     * Get the user provider configuration.
	 * 获取用户提供程序配置
     *
     * @param  string|null  $provider
     * @return array|null
     */
    protected function getProviderConfiguration($provider)
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->app['config']['auth.providers.'.$provider];
        }
    }

    /**
     * Create an instance of the database user provider.
	 * 创建数据库用户提供程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Auth\DatabaseUserProvider
     */
    protected function createDatabaseProvider($config)
    {
        $connection = $this->app['db']->connection($config['connection'] ?? null);

        return new DatabaseUserProvider($connection, $this->app['hash'], $config['table']);
    }

    /**
     * Create an instance of the Eloquent user provider.
	 * 创建Eloquent用户提供程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Auth\EloquentUserProvider
     */
    protected function createEloquentProvider($config)
    {
        return new EloquentUserProvider($this->app['hash'], $config['model']);
    }

    /**
     * Get the default user provider name.
	 * 获取默认用户提供程序名称
     *
     * @return string
     */
    public function getDefaultUserProvider()
    {
        return $this->app['config']['auth.defaults.provider'];
    }
}
