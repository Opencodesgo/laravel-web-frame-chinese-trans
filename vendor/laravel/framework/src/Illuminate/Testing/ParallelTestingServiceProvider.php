<?php
/**
 * Illuminate, 测试, 并行测试服务提供商
 */

namespace Illuminate\Testing;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\Concerns\TestDatabases;

class ParallelTestingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    use TestDatabases;

    /**
     * Boot the application's service providers.
	 * 引导应用程序的服务提供者
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->bootTestDatabase();
        }
    }

    /**
     * Register the service provider.
	 * 注册服务提供者
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->app->singleton(ParallelTesting::class, function () {
                return new ParallelTesting($this->app);
            });
        }
    }
}
