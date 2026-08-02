<?php
/**
 * Illuminate，支持，综合服务提供者
 */

namespace Illuminate\Support;

class AggregateServiceProvider extends ServiceProvider
{
    /**
     * The provider class names.
	 * 提供程序类名
     *
     * @var array
     */
    protected $providers = [];

    /**
     * An array of the service provider instances.
	 * 服务提供者实例的数组
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Register the service provider.
	 * 注册服务提供者
     *
     * @return void
     */
    public function register()
    {
        $this->instances = [];

        foreach ($this->providers as $provider) {
            $this->instances[] = $this->app->register($provider);
        }
    }

    /**
     * Get the services provided by the provider.
	 * 获取提供者提供的服务
     *
     * @return array
     */
    public function provides()
    {
        $provides = [];

        foreach ($this->providers as $provider) {
            $instance = $this->app->resolveProvider($provider);

            $provides = array_merge($provides, $instance->provides());
        }

        return $provides;
    }
}
