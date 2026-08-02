<?php
/**
 * Illuminate，Session会话，会话服务提供者
 * 服务容器绑定session
 */

namespace Illuminate\Session;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
	 * 注册服务提供者
     *
     * @return void
     */
    public function register()
    {
        $this->registerSessionManager();

        $this->registerSessionDriver();

        $this->app->singleton(StartSession::class, function () {
            return new StartSession($this->app->make(SessionManager::class), function () {
                return $this->app->make(CacheFactory::class);
            });
        });
    }

    /**
     * Register the session manager instance.
	 * 注册会话管理实例
     *
     * @return void
     */
    protected function registerSessionManager()
    {
        $this->app->singleton('session', function ($app) {
            return new SessionManager($app);
        });
    }

    /**
     * Register the session driver instance.
	 * 注册会话驱动实例
     *
     * @return void
     */
    protected function registerSessionDriver()
    {
        $this->app->singleton('session.store', function ($app) {
            // First, we will create the session manager which is responsible for the
            // creation of the various session drivers when they are needed by the
            // application instance, and will resolve them on a lazy load basis.
			// 首先，我们将创建会话管理器，它负责控件需要时创建各种会话驱动程序。
            return $app->make('session')->driver();
        });
    }
}
